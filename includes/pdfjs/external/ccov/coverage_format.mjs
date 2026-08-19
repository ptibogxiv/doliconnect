/* Copyright 2026 Mozilla Foundation
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

const COVERAGE_FORMAT_TO_REPORTER = {
  info: "lcovonly",
  html: "html",
  json: "json",
  text: "text",
  cobertura: "cobertura",
  clover: "clover",
};

function parseCoverageFormats(str) {
  const formats = new Set();
  for (const fmt of (str ?? "").split(",")) {
    const name = fmt.trim();
    if (name && COVERAGE_FORMAT_TO_REPORTER[name]) {
      formats.add(name);
    } else if (name) {
      console.warn(
        `### Unknown coverage format "${name}", valid values: ${Object.keys(COVERAGE_FORMAT_TO_REPORTER).join(", ")}`
      );
    }
  }
  return formats.size > 0 ? formats : new Set(["info"]);
}

export { COVERAGE_FORMAT_TO_REPORTER, parseCoverageFormats };
