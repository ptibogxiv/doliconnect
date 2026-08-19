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

import kleur from "kleur";

kleur.enabled =
  !process.env.NO_COLOR &&
  (!!process.stdout.isTTY ||
    !!process.env.FORCE_COLOR ||
    process.env.GITHUB_ACTIONS === "true");

const TEST_PASSED = kleur.green("TEST-PASS");
const TEST_UNEXPECTED_FAIL = kleur.red().bold("TEST-UNEXPECTED-FAIL");

function colorBrowser(name) {
  return kleur.cyan(name);
}

export { colorBrowser, TEST_PASSED, TEST_UNEXPECTED_FAIL };
