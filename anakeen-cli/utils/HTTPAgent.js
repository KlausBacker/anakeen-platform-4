const console = require("console");
const fs = require("fs");
const path = require("path");
const util = require("util");
const fetch = require("node-fetch");

const GenericError = require(path.resolve(__dirname, "GenericError.js"));
const Tmp = require(path.resolve(__dirname, "Tmp.js"));
const { HTTPCredentialStore } = require(path.resolve(__dirname, "HTTPCredentialStore.js"));
const Utils = require(path.resolve(__dirname, "Utils.js"));

const fs_rename = util.promisify(fs.rename);

class HTTPAgentError extends GenericError {}

class HTTPAgent {
  constructor(options = {}) {
    if (typeof options !== "object") {
      throw new HTTPAgentError(`'options' is not an object (found ${typeof options} instead)`);
    }
    this._debug = options.hasOwnProperty("debug") && options.debug === true;
  }

  setAuthorizationHeader(headers, authUser, authPassword) {
    if (authUser) {
      let data = authUser + ":";
      if (authPassword) {
        data = data + authPassword;
      }
      let buff = Buffer.from(data, "utf8");
      headers["Authorization"] = "Basic " + buff.toString("base64");
    }
  }

  debug(msg) {
    if (typeof console === "object" && this._debug) {
      console.log(msg);
    }
  }

  async getHeadersForUrl(url) {
    let headers = {};
    let httpCredentialStore = new HTTPCredentialStore();
    await httpCredentialStore.loadCredentialStore();
    let credential = httpCredentialStore.getCredentialForUrl(url);
    if (credential !== null) {
      this.setAuthorizationHeader(headers, credential.authUser, credential.authPassword);
    }
    return headers;
  }

  async fetch(url) {
    url = Utils.normalizeUrl(url);
    const headers = await this.getHeadersForUrl(url);
    return await fetch(url, {
      headers: headers
    });
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

    this.debug(`[HTTPAgent] Downloading '${url}' to temporary file '${tmpName}'`);

    const headers = await this.getHeadersForUrl(url);
    const response = await fetch(url, {
      headers: headers
    });
    const outputStream = fs.createWriteStream(tmpName);
    await new Promise((resolve, reject) => {
      try {
        if (!response.ok) {
          reject(new HTTPAgentError(`Unexpected HTTP status ${response.status} ('${response.statusText}')`));
        }
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

module.exports = { HTTPAgent, HTTPAgentError };
