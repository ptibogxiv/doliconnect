/* Copyright 2017 Mozilla Foundation
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

/** @typedef {import("./interfaces").IL10n} IL10n */

import { FluentBundle, FluentResource } from "fluent-bundle";
import { DOMLocalization } from "fluent-dom";
import { fetchData } from "pdfjs-lib";
import { L10n } from "./l10n.js";

function createBundle(lang, text) {
  const resource = new FluentResource(text);
  const bundle = new FluentBundle(lang);
  const errors = bundle.addResource(resource);
  if (errors.length) {
    console.error("L10n errors", errors);
  }
  return bundle;
}

/**
 * @implements {IL10n}
 */
class GenericL10n extends L10n {
  constructor(lang) {
    super({ lang });

    const generateBundles = !lang
      ? GenericL10n.#generateBundlesFallback.bind(
          GenericL10n,
          this.getLanguage()
        )
      : GenericL10n.#generateBundles.bind(
          GenericL10n,
          "en-us",
          this.getLanguage()
        );
    this._setL10n(new DOMLocalization([], generateBundles));
  }

  /**
   * Generate the bundles for Fluent.
   * @param {String} defaultLang - The fallback language to use for
   *   translations.
   * @param {String} baseLang - The base language to use for translations.
   */
  static async *#generateBundles(defaultLang, baseLang) {
    const { baseURL, paths } = await this.#getPaths();

    const langs = [baseLang];
    if (defaultLang !== baseLang) {
      // Also fallback to the short-format of the base language
      // (see issue 17269).
      const shortLang = baseLang.split("-", 1)[0];

      if (shortLang !== baseLang) {
        langs.push(shortLang);
      }
      langs.push(defaultLang);
    }
    for (const lang of langs) {
      const bundle = await this.#createBundle(lang, baseURL, paths);
      if (bundle) {
        yield bundle;
      }
      if (lang === "en-us") {
        yield this.#createBundleFallback(lang);
      }
    }
  }

  static async #createBundle(lang, baseURL, paths) {
    const path = paths[lang];
    if (!path) {
      return null;
    }
    const url = new URL(path, baseURL);
    const text = await fetchData(url, /* type = */ "text");

    return createBundle(lang, text);
  }

  static async #getPaths() {
    try {
      const { href } = document.querySelector(`link[type="application/l10n"]`);
      const paths = await fetchData(href, /* type = */ "json");

      return { baseURL: href.replace(/[^/]*$/, "") || "./", paths };
    } catch {}
    return { baseURL: "./", paths: Object.create(null) };
  }

  static async *#generateBundlesFallback(lang) {
    yield this.#createBundleFallback(lang);
  }

  static async #createBundleFallback(lang) {
    if (typeof PDFJSDev !== "undefined" && PDFJSDev.test("TESTING")) {
      throw new Error("Not implemented: #createBundleFallback");
    }
    const text =
      typeof PDFJSDev === "undefined"
        ? await fetchData(
            new URL("../l10n/en-US/viewer.ftl", window.location.href),
            /* type = */ "text"
          )
        : PDFJSDev.eval("DEFAULT_FTL");

    return createBundle(lang, text);
  }
}

export { GenericL10n };
