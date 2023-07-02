/* Copyright 2012 Mozilla Foundation
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
  createValidAbsoluteUrl,
  FeatureTest,
  shadow,
  unreachable,
  warn,
} from "../shared/util.js";
import { ChunkedStreamManager } from "./chunked_stream.js";
import { MissingDataException } from "./core_utils.js";
import { PDFDocument } from "./document.js";
import { Stream } from "./stream.js";

function parseDocBaseUrl(url) {
  if (url) {
    const absoluteUrl = createValidAbsoluteUrl(url);
    if (absoluteUrl) {
      return absoluteUrl.href;
    }
    warn(`Invalid absolute docBaseUrl: "${url}".`);
  }
  return null;
}

class BasePdfManager {
  constructor(args) {
    if (this.constructor === BasePdfManager) {
      unreachable("Cannot initialize BasePdfManager.");
    }
    this._docBaseUrl = parseDocBaseUrl(args.docBaseUrl);
    this._docId = args.docId;
    this._password = args.password;
    this.enableXfa = args.enableXfa;

    // Check `OffscreenCanvas` support once, rather than repeatedly throughout
    // the worker-thread code.
    args.evaluatorOptions.isOffscreenCanvasSupported &&=
      FeatureTest.isOffscreenCanvasSupported;
    this.evaluatorOptions = args.evaluatorOptions;
  }

  get docId() {
    return this._docId;
  }

  get password() {
    return this._password;
  }

  get docBaseUrl() {
    const catalog = this.pdfDocument.catalog;
    return shadow(this, "docBaseUrl", catalog.baseUrl || this._docBaseUrl);
  }

  ensureDoc(prop, args) {
    return this.ensure(this.pdfDocument, prop, args);
  }

  ensureXRef(prop, args) {
    return this.ensure(this.pdfDocument.xref, prop, args);
  }

  ensureCatalog(prop, args) {
    return this.ensure(this.pdfDocument.catalog, prop, args);
  }

  getPage(pageIndex) {
    return this.pdfDocument.getPage(pageIndex);
  }

  fontFallback(id, handler) {
    return this.pdfDocument.fontFallback(id, handler);
  }

  loadXfaFonts(handler, task) {
    return this.pdfDocument.loadXfaFonts(handler, task);
  }

  loadXfaImages() {
    return this.pdfDocument.loadXfaImages();
  }

  serializeXfaData(annotationStorage) {
    return this.pdfDocument.serializeXfaData(annotationStorage);
  }

  cleanup(manuallyTriggered = false) {
    return this.pdfDocument.cleanup(manuallyTriggered);
  }

  async ensure(obj, prop, args) {
    unreachable("Abstract method `ensure` called");
  }

  requestRange(begin, end) {
    unreachable("Abstract method `requestRange` called");
  }

  requestLoadedStream(noFetch = false) {
    unreachable("Abstract method `requestLoadedStream` called");
  }

  sendProgressiveData(chunk) {
    unreachable("Abstract method `sendProgressiveData` called");
  }

  updatePassword(password) {
    this._password = password;
  }

  terminate(reason) {
    unreachable("Abstract method `terminate` called");
  }
}

class LocalPdfManager extends BasePdfManager {
  constructor(args) {
    super(args);

    const stream = new Stream(args.source);
    this.pdfDocument = new PDFDocument(this, stream);
    this._loadedStreamPromise = Promise.resolve(stream);
  }

  async ensure(obj, prop, args) {
    const value = obj[prop];
    if (typeof value === "function") {
      return value.apply(obj, args);
    }
    return value;
  }

  requestRange(begin, end) {
    return Promise.resolve();
  }

  requestLoadedStream(noFetch = false) {
    return this._loadedStreamPromise;
  }

  terminate(reason) {}
}

class NetworkPdfManager extends BasePdfManager {
  constructor(args) {
    super(args);

    this.streamManager = new ChunkedStreamManager(args.source, {
      msgHandler: args.handler,
      length: args.length,
      disableAutoFetch: args.disableAutoFetch,
      rangeChunkSize: args.rangeChunkSize,
    });
    this.pdfDocument = new PDFDocument(this, this.streamManager.getStream());
  }

  async ensure(obj, prop, args) {
    try {
      const value = obj[prop];
      if (typeof value === "function") {
        return value.apply(obj, args);
      }
      return value;
    } catch (ex) {
      if (!(ex instanceof MissingDataException)) {
        throw ex;
      }
      await this.requestRange(ex.begin, ex.end);
      return this.ensure(obj, prop, args);
    }
  }

  requestRange(begin, end) {
    return this.streamManager.requestRange(begin, end);
  }

  requestLoadedStream(noFetch = false) {
    return this.streamManager.requestAllChunks(noFetch);
  }

  sendProgressiveData(chunk) {
    this.streamManager.onReceiveData({ chunk });
  }

  terminate(reason) {
    this.streamManager.abort(reason);
  }
}

export { LocalPdfManager, NetworkPdfManager };
