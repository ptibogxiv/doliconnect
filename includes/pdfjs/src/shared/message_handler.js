/* Copyright 2018 Mozilla Foundation
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

import {
  AbortException,
  assert,
  MissingPDFException,
  PasswordException,
  PromiseCapability,
  UnexpectedResponseException,
  UnknownErrorException,
  unreachable,
} from "./util.js";

const CallbackKind = {
  UNKNOWN: 0,
  DATA: 1,
  ERROR: 2,
};

const StreamKind = {
  UNKNOWN: 0,
  CANCEL: 1,
  CANCEL_COMPLETE: 2,
  CLOSE: 3,
  ENQUEUE: 4,
  ERROR: 5,
  PULL: 6,
  PULL_COMPLETE: 7,
  START_COMPLETE: 8,
};

function wrapReason(reason) {
  if (
    !(
      reason instanceof Error ||
      (typeof reason === "object" && reason !== null)
    )
  ) {
    unreachable(
      'wrapReason: Expected "reason" to be a (possibly cloned) Error.'
    );
  }
  switch (reason.name) {
    case "AbortException":
      return new AbortException(reason.message);
    case "MissingPDFException":
      return new MissingPDFException(reason.message);
    case "PasswordException":
      return new PasswordException(reason.message, reason.code);
    case "UnexpectedResponseException":
      return new UnexpectedResponseException(reason.message, reason.status);
    case "UnknownErrorException":
      return new UnknownErrorException(reason.message, reason.details);
    default:
      return new UnknownErrorException(reason.message, reason.toString());
  }
}

class MessageHandler {
  constructor(sourceName, targetName, comObj) {
    this.sourceName = sourceName;
    this.targetName = targetName;
    this.comObj = comObj;
    this.callbackId = 1;
    this.streamId = 1;
    this.streamSinks = Object.create(null);
    this.streamControllers = Object.create(null);
    this.callbackCapabilities = Object.create(null);
    this.actionHandler = Object.create(null);

    this._onComObjOnMessage = event => {
      const data = event.data;
      if (data.targetName !== this.sourceName) {
        return;
      }
      if (data.stream) {
        this.#processStreamMessage(data);
        return;
      }
      if (data.callback) {
        const callbackId = data.callbackId;
        const capability = this.callbackCapabilities[callbackId];
        if (!capability) {
          throw new Error(`Cannot resolve callback ${callbackId}`);
        }
        delete this.callbackCapabilities[callbackId];

        if (data.callback === CallbackKind.DATA) {
          capability.resolve(data.data);
        } else if (data.callback === CallbackKind.ERROR) {
          capability.reject(wrapReason(data.reason));
        } else {
          throw new Error("Unexpected callback case");
        }
        return;
      }
      const action = this.actionHandler[data.action];
      if (!action) {
        throw new Error(`Unknown action from worker: ${data.action}`);
      }
      if (data.callbackId) {
        const cbSourceName = this.sourceName;
        const cbTargetName = data.sourceName;

        new Promise(function (resolve) {
          resolve(action(data.data));
        }).then(
          function (result) {
            comObj.postMessage({
              sourceName: cbSourceName,
              targetName: cbTargetName,
              callback: CallbackKind.DATA,
              callbackId: data.callbackId,
              data: result,
            });
          },
          function (reason) {
            comObj.postMessage({
              sourceName: cbSourceName,
              targetName: cbTargetName,
              callback: CallbackKind.ERROR,
              callbackId: data.callbackId,
              reason: wrapReason(reason),
            });
          }
        );
        return;
      }
      if (data.streamId) {
        this.#createStreamSink(data);
        return;
      }
      action(data.data);
    };
    comObj.addEventListener("message", this._onComObjOnMessage);
  }

  on(actionName, handler) {
    if (typeof PDFJSDev === "undefined" || PDFJSDev.test("TESTING")) {
      assert(
        typeof handler === "function",
        'MessageHandler.on: Expected "handler" to be a function.'
      );
    }
    const ah = this.actionHandler;
    if (ah[actionName]) {
      throw new Error(`There is already an actionName called "${actionName}"`);
    }
    ah[actionName] = handler;
  }

  /**
   * Sends a message to the comObj to invoke the action with the supplied data.
   * @param {string} actionName - Action to call.
   * @param {JSON} data - JSON data to send.
   * @param {Array} [transfers] - List of transfers/ArrayBuffers.
   */
  send(actionName, data, transfers) {
    this.comObj.postMessage(
      {
        sourceName: this.sourceName,
        targetName: this.targetName,
        action: actionName,
        data,
      },
      transfers
    );
  }

  /**
   * Sends a message to the comObj to invoke the action with the supplied data.
   * Expects that the other side will callback with the response.
   * @param {string} actionName - Action to call.
   * @param {JSON} data - JSON data to send.
   * @param {Array} [transfers] - List of transfers/ArrayBuffers.
   * @returns {Promise} Promise to be resolved with response data.
   */
  sendWithPromise(actionName, data, transfers) {
    const callbackId = this.callbackId++;
    const capability = new PromiseCapability();
    this.callbackCapabilities[callbackId] = capability;
    try {
      this.comObj.postMessage(
        {
          sourceName: this.sourceName,
          targetName: this.targetName,
          action: actionName,
          callbackId,
          data,
        },
        transfers
      );
    } catch (ex) {
      capability.reject(ex);
    }
    return capability.promise;
  }

  /**
   * Sends a message to the comObj to invoke the action with the supplied data.
   * Expect that the other side will callback to signal 'start_complete'.
   * @param {string} actionName - Action to call.
   * @param {JSON} data - JSON data to send.
   * @param {Object} queueingStrategy - Strategy to signal backpressure based on
   *                 internal queue.
   * @param {Array} [transfers] - List of transfers/ArrayBuffers.
   * @returns {ReadableStream} ReadableStream to read data in chunks.
   */
  sendWithStream(actionName, data, queueingStrategy, transfers) {
    const streamId = this.streamId++,
      sourceName = this.sourceName,
      targetName = this.targetName,
      comObj = this.comObj;

    return new ReadableStream(
      {
        start: controller => {
          const startCapability = new PromiseCapability();
          this.streamControllers[streamId] = {
            controller,
            startCall: startCapability,
            pullCall: null,
            cancelCall: null,
            isClosed: false,
          };
          comObj.postMessage(
            {
              sourceName,
              targetName,
              action: actionName,
              streamId,
              data,
              desiredSize: controller.desiredSize,
            },
            transfers
          );
          // Return Promise for Async process, to signal success/failure.
          return startCapability.promise;
        },

        pull: controller => {
          const pullCapability = new PromiseCapability();
          this.streamControllers[streamId].pullCall = pullCapability;
          comObj.postMessage({
            sourceName,
            targetName,
            stream: StreamKind.PULL,
            streamId,
            desiredSize: controller.desiredSize,
          });
          // Returning Promise will not call "pull"
          // again until current pull is resolved.
          return pullCapability.promise;
        },

        cancel: reason => {
          assert(reason instanceof Error, "cancel must have a valid reason");
          const cancelCapability = new PromiseCapability();
          this.streamControllers[streamId].cancelCall = cancelCapability;
          this.streamControllers[streamId].isClosed = true;
          comObj.postMessage({
            sourceName,
            targetName,
            stream: StreamKind.CANCEL,
            streamId,
            reason: wrapReason(reason),
          });
          // Return Promise to signal success or failure.
          return cancelCapability.promise;
        },
      },
      queueingStrategy
    );
  }

  #createStreamSink(data) {
    const streamId = data.streamId,
      sourceName = this.sourceName,
      targetName = data.sourceName,
      comObj = this.comObj;
    const self = this,
      action = this.actionHandler[data.action];

    const streamSink = {
      enqueue(chunk, size = 1, transfers) {
        if (this.isCancelled) {
          return;
        }
        const lastDesiredSize = this.desiredSize;
        this.desiredSize -= size;
        // Enqueue decreases the desiredSize property of sink,
        // so when it changes from positive to negative,
        // set ready as unresolved promise.
        if (lastDesiredSize > 0 && this.desiredSize <= 0) {
          this.sinkCapability = new PromiseCapability();
          this.ready = this.sinkCapability.promise;
        }
        comObj.postMessage(
          {
            sourceName,
            targetName,
            stream: StreamKind.ENQUEUE,
            streamId,
            chunk,
          },
          transfers
        );
      },

      close() {
        if (this.isCancelled) {
          return;
        }
        this.isCancelled = true;
        comObj.postMessage({
          sourceName,
          targetName,
          stream: StreamKind.CLOSE,
          streamId,
        });
        delete self.streamSinks[streamId];
      },

      error(reason) {
        assert(reason instanceof Error, "error must have a valid reason");
        if (this.isCancelled) {
          return;
        }
        this.isCancelled = true;
        comObj.postMessage({
          sourceName,
          targetName,
          stream: StreamKind.ERROR,
          streamId,
          reason: wrapReason(reason),
        });
      },

      sinkCapability: new PromiseCapability(),
      onPull: null,
      onCancel: null,
      isCancelled: false,
      desiredSize: data.desiredSize,
      ready: null,
    };

    streamSink.sinkCapability.resolve();
    streamSink.ready = streamSink.sinkCapability.promise;
    this.streamSinks[streamId] = streamSink;

    new Promise(function (resolve) {
      resolve(action(data.data, streamSink));
    }).then(
      function () {
        comObj.postMessage({
          sourceName,
          targetName,
          stream: StreamKind.START_COMPLETE,
          streamId,
          success: true,
        });
      },
      function (reason) {
        comObj.postMessage({
          sourceName,
          targetName,
          stream: StreamKind.START_COMPLETE,
          streamId,
          reason: wrapReason(reason),
        });
      }
    );
  }

  #processStreamMessage(data) {
    const streamId = data.streamId,
      sourceName = this.sourceName,
      targetName = data.sourceName,
      comObj = this.comObj;
    const streamController = this.streamControllers[streamId],
      streamSink = this.streamSinks[streamId];

    switch (data.stream) {
      case StreamKind.START_COMPLETE:
        if (data.success) {
          streamController.startCall.resolve();
        } else {
          streamController.startCall.reject(wrapReason(data.reason));
        }
        break;
      case StreamKind.PULL_COMPLETE:
        if (data.success) {
          streamController.pullCall.resolve();
        } else {
          streamController.pullCall.reject(wrapReason(data.reason));
        }
        break;
      case StreamKind.PULL:
        // Ignore any pull after close is called.
        if (!streamSink) {
          comObj.postMessage({
            sourceName,
            targetName,
            stream: StreamKind.PULL_COMPLETE,
            streamId,
            success: true,
          });
          break;
        }
        // Pull increases the desiredSize property of sink, so when it changes
        // from negative to positive, set ready property as resolved promise.
        if (streamSink.desiredSize <= 0 && data.desiredSize > 0) {
          streamSink.sinkCapability.resolve();
        }
        // Reset desiredSize property of sink on every pull.
        streamSink.desiredSize = data.desiredSize;

        new Promise(function (resolve) {
          resolve(streamSink.onPull?.());
        }).then(
          function () {
            comObj.postMessage({
              sourceName,
              targetName,
              stream: StreamKind.PULL_COMPLETE,
              streamId,
              success: true,
            });
          },
          function (reason) {
            comObj.postMessage({
              sourceName,
              targetName,
              stream: StreamKind.PULL_COMPLETE,
              streamId,
              reason: wrapReason(reason),
            });
          }
        );
        break;
      case StreamKind.ENQUEUE:
        assert(streamController, "enqueue should have stream controller");
        if (streamController.isClosed) {
          break;
        }
        streamController.controller.enqueue(data.chunk);
        break;
      case StreamKind.CLOSE:
        assert(streamController, "close should have stream controller");
        if (streamController.isClosed) {
          break;
        }
        streamController.isClosed = true;
        streamController.controller.close();
        this.#deleteStreamController(streamController, streamId);
        break;
      case StreamKind.ERROR:
        assert(streamController, "error should have stream controller");
        streamController.controller.error(wrapReason(data.reason));
        this.#deleteStreamController(streamController, streamId);
        break;
      case StreamKind.CANCEL_COMPLETE:
        if (data.success) {
          streamController.cancelCall.resolve();
        } else {
          streamController.cancelCall.reject(wrapReason(data.reason));
        }
        this.#deleteStreamController(streamController, streamId);
        break;
      case StreamKind.CANCEL:
        if (!streamSink) {
          break;
        }

        new Promise(function (resolve) {
          resolve(streamSink.onCancel?.(wrapReason(data.reason)));
        }).then(
          function () {
            comObj.postMessage({
              sourceName,
              targetName,
              stream: StreamKind.CANCEL_COMPLETE,
              streamId,
              success: true,
            });
          },
          function (reason) {
            comObj.postMessage({
              sourceName,
              targetName,
              stream: StreamKind.CANCEL_COMPLETE,
              streamId,
              reason: wrapReason(reason),
            });
          }
        );
        streamSink.sinkCapability.reject(wrapReason(data.reason));
        streamSink.isCancelled = true;
        delete this.streamSinks[streamId];
        break;
      default:
        throw new Error("Unexpected stream case");
    }
  }

  async #deleteStreamController(streamController, streamId) {
    // Delete the `streamController` only when the start, pull, and cancel
    // capabilities have settled, to prevent `TypeError`s.
    await Promise.allSettled([
      streamController.startCall?.promise,
      streamController.pullCall?.promise,
      streamController.cancelCall?.promise,
    ]);
    delete this.streamControllers[streamId];
  }

  destroy() {
    this.comObj.removeEventListener("message", this._onComObjOnMessage);
  }
}

export { MessageHandler };
