/* Copyright 2020 Mozilla Foundation
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

class ProxyHandler {
  constructor() {
    // Don't dispatch an event for those properties.
    //  - delay: allow to delay field redraw until delay is set to false.
    //    Likely it's useless to implement that stuff.
    this.nosend = new Set(["delay"]);
  }

  get(obj, prop) {
    // script may add some properties to the object
    if (prop in obj._expandos) {
      const val = obj._expandos[prop];
      if (typeof val === "function") {
        return val.bind(obj);
      }
      return val;
    }

    if (typeof prop === "string" && !prop.startsWith("_") && prop in obj) {
      // return only public properties
      // i.e. the ones not starting with a '_'
      const val = obj[prop];
      if (typeof val === "function") {
        return val.bind(obj);
      }
      return val;
    }

    return undefined;
  }

  set(obj, prop, value) {
    if (obj._kidIds) {
      // If the field is a container for other fields then
      // dispatch the kids.
      obj._kidIds.forEach(id => {
        obj._appObjects[id].wrapped[prop] = value;
      });
    }

    if (typeof prop === "string" && !prop.startsWith("_") && prop in obj) {
      const old = obj[prop];
      obj[prop] = value;
      if (
        !this.nosend.has(prop) &&
        obj._send &&
        obj._id !== null &&
        typeof old !== "function"
      ) {
        const data = { id: obj._id };
        data[prop] = prop === "value" ? obj._getValue() : obj[prop];

        // send the updated value to the other side
        if (!obj._siblings) {
          obj._send(data);
        } else {
          data.siblings = obj._siblings;
          obj._send(data);
        }
      }
    } else {
      obj._expandos[prop] = value;
    }
    return true;
  }

  has(obj, prop) {
    return (
      prop in obj._expandos ||
      (typeof prop === "string" && !prop.startsWith("_") && prop in obj)
    );
  }

  getPrototypeOf(obj) {
    return null;
  }

  setPrototypeOf(obj, proto) {
    return false;
  }

  isExtensible(obj) {
    return true;
  }

  preventExtensions(obj) {
    return false;
  }

  getOwnPropertyDescriptor(obj, prop) {
    if (prop in obj._expandos) {
      return {
        configurable: true,
        enumerable: true,
        value: obj._expandos[prop],
      };
    }

    if (typeof prop === "string" && !prop.startsWith("_") && prop in obj) {
      return { configurable: true, enumerable: true, value: obj[prop] };
    }

    return undefined;
  }

  defineProperty(obj, key, descriptor) {
    Object.defineProperty(obj._expandos, key, descriptor);
    return true;
  }

  deleteProperty(obj, prop) {
    if (prop in obj._expandos) {
      delete obj._expandos[prop];
    }
  }

  ownKeys(obj) {
    const fromExpandos = Reflect.ownKeys(obj._expandos);
    const fromObj = Reflect.ownKeys(obj).filter(k => !k.startsWith("_"));
    return fromExpandos.concat(fromObj);
  }
}

export { ProxyHandler };
