/* Copyright 2021 Mozilla Foundation
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

const { closePages, loadAndWait } = require("./test_utils.js");

describe("accessibility", () => {
  describe("structure tree", () => {
    let pages;

    beforeAll(async () => {
      pages = await loadAndWait("structure_simple.pdf", ".structTree");
    });

    afterAll(async () => {
      await closePages(pages);
    });

    it("must build structure that maps to text layer", async () => {
      await Promise.all(
        pages.map(async ([browserName, page]) => {
          await page.waitForSelector(".structTree");

          // Check the headings match up.
          const head1 = await page.$eval(
            ".structTree [role='heading'][aria-level='1'] span",
            el =>
              document.getElementById(el.getAttribute("aria-owns")).textContent
          );
          expect(head1).withContext(`In ${browserName}`).toEqual("Heading 1");
          const head2 = await page.$eval(
            ".structTree [role='heading'][aria-level='2'] span",
            el =>
              document.getElementById(el.getAttribute("aria-owns")).textContent
          );
          expect(head2).withContext(`In ${browserName}`).toEqual("Heading 2");

          // Check the order of the content.
          const texts = await page.$$eval(".structTree [aria-owns]", nodes =>
            nodes.map(
              el =>
                document.getElementById(el.getAttribute("aria-owns"))
                  .textContent
            )
          );
          expect(texts)
            .withContext(`In ${browserName}`)
            .toEqual([
              "Heading 1",
              "This paragraph 1.",
              "Heading 2",
              "This paragraph 2.",
            ]);
        })
      );
    });
  });

  describe("Annotation", () => {
    let pages;

    beforeAll(async () => {
      pages = await loadAndWait(
        "tracemonkey_a11y.pdf",
        ".textLayer .endOfContent"
      );
    });

    afterAll(async () => {
      await closePages(pages);
    });

    function getSpans(page) {
      return page.evaluate(() => {
        const elements = document.querySelectorAll(
          `.textLayer span[aria-owns]:not([role="presentation"])`
        );
        const results = [];
        for (const element of elements) {
          results.push(element.innerText);
        }
        return results;
      });
    }

    it("must check that some spans are linked to some annotations thanks to aria-owns", async () => {
      await Promise.all(
        pages.map(async ([browserName, page]) => {
          const spanContents = await getSpans(page);

          expect(spanContents)
            .withContext(`In ${browserName}`)
            .toEqual(["Languages", "@intel.com", "Abstract", "Introduction"]);
        })
      );
    });
  });

  describe("Annotations order", () => {
    let pages;

    beforeAll(async () => {
      pages = await loadAndWait("fields_order.pdf", ".annotationLayer");
    });

    afterAll(async () => {
      await closePages(pages);
    });

    it("must check that the text fields are in the visual order", async () => {
      await Promise.all(
        pages.map(async ([browserName, page]) => {
          const ids = await page.evaluate(() => {
            const elements = document.querySelectorAll(
              ".annotationLayer .textWidgetAnnotation"
            );
            const results = [];
            for (const element of elements) {
              results.push(element.getAttribute("data-annotation-id"));
            }
            return results;
          });

          expect(ids)
            .withContext(`In ${browserName}`)
            .toEqual(["32R", "30R", "31R", "34R", "29R", "33R"]);
        })
      );
    });
  });
});
