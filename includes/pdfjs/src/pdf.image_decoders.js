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

import { getVerbosityLevel, setVerbosityLevel } from "./shared/util.js";
import { Jbig2Image } from "./core/jbig2.js";
import { JpegImage } from "./core/jpg.js";
import { JpxImage } from "./core/jpx.js";

// eslint-disable-next-line no-unused-vars
const pdfjsVersion = PDFJSDev.eval("BUNDLE_VERSION");
// eslint-disable-next-line no-unused-vars
const pdfjsBuild = PDFJSDev.eval("BUNDLE_BUILD");

export {
  getVerbosityLevel,
  Jbig2Image,
  JpegImage,
  JpxImage,
  setVerbosityLevel,
};
