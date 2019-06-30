const console = require("console");
const fs = require("fs");
const path = require("path");
const util = require("util");
const fetch = require("node-fetch");

const GenericError = require(path.resolve(__dirname, "GenericError.js"));
const Tmp = require(path.resolve(__dirname, "Tmp.js"));

const fs_rename = util.promisify(fs.rename);

function normalizeUrl(url) {
  /* Normalize hostname (to lowercase) in URL */
  const u = new URL(url);
  /* Remove duplicates slashes */
  u.pathname = u.pathname.replace(/\/\/+/, "/");
  return u.toString();
}

class HTTPAgentError extends GenericError {}

class HTTPAgent {
  constructor(options = {}) {
    if (typeof options !== "object") {
      throw new HTTPAgentError(
        `'options' is not an object (found ${typeof options} instead)`
      );
    }
    this._debug = options.hasOwnProperty("debug") && options.debug === true;
  }

  debug(msg) {
    if (typeof console === "object" && this._debug) {
      console.log(msg);
    }
  }

  async fetch(url) {
    url = normalizeUrl(url);
    return await fetch(url);
  }

  /**
   * @param {string} url URL to download
   * @param {string} dir Where the temporary downloaded file will be created
   * @returns {Promise<void>}
   */
  async downloadTmpFile(url, dir = undefined) {
    const { name: tmpName } = await Tmp.tmpfile({
      dir: dir,
      discardDescriptor: true,
      keep: true
    });

    this.debug(
      `[HTTPAgent] Downloading '${url}' to temporary file '${tmpName}'`
    );

    const response = await fetch(url);
    const outputStream = fs.createWriteStream(tmpName);
    await new Promise((resolve, reject) => {
      try {
        response.body
          .pipe(outputStream)
          .on("finish", resolve)
          .on("error", reject);
      } catch (e) {
        reject(e);
      }
    });
    return tmpName;
  }

  /**
   * @param {string} url URL to download
   * @param {string} pathname File's pathname where the content will be saved
   * @returns {Promise<string>}
   */
  async downloadFileTo(url, pathname) {
    const tmp = await this.downloadTmpFile(url, path.dirname(pathname));
    await fs_rename(tmp, pathname);
    return pathname;
  }
}

module.exports = { HTTPAgent, HTTPAgentError, normalizeUrl };
