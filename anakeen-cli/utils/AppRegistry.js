const path = require("path");
const semver = require("semver");

const GenericError = require(path.resolve(__dirname, "GenericError.js"));
const { HTTPAgent } = require(path.resolve(__dirname, "HTTPAgent.js"));

class AppRegistryError extends GenericError {}

class AppRegistry {
  /**
   * @param {string} url Registry's base URL
   * @param {string} authUser (optional) HTTP auth username
   * @param {string} authPassword (optional) HTTP auth password
   */
  constructor({ url, authUser, authPassword }) {
    this._index = undefined;
    this.url = url.trim().replace(/\/+$/, "");
    /* TODO: Implement HTTP Basic authentication. */
    this.authUser = authUser;
    this.authPassword = authPassword;
  }

  /**
   * @returns {Promise<AppRegistry>}
   */
  async refreshIndex() {
    const url = this.getURL();
    const agent = new HTTPAgent();
    const response = await agent.fetch(url);
    if (!response.ok) {
      throw new AppRegistryError(`Could not get content from registry at URL '${url}'`);
    }
    const data = await response.text();
    const index = JSON.parse(data);
    if (!Array.isArray(index)) {
      throw new AppRegistryError(
        `Malformed response from registry at URL '${url}': ` + `${response.status} ${response.statusText}\n` + data
      );
    }
    this._index = index;
    return this;
  }

  /**
   * @returns {Promise<AppRegistry>}
   */
  async refreshIndexIfUndefined() {
    if (!Array.isArray(this._index)) {
      this.refreshIndex();
    }
    return this;
  }

  /**
   * @returns {string}
   */
  getURL() {
    return this.url;
  }

  getModuleVersionURL(moduleName, moduleVersion) {
    return [this.getURL(), encodeURI(moduleName), encodeURI(moduleVersion)].join("/");
  }

  /**
   * @returns {Promise<boolean>}
   */
  async ping() {
    try {
      await this.refreshIndex();
    } catch (e) {
      return false;
    }
    return true;
  }

  /**
   * @param {string} name
   * @returns {Promise<boolean>}
   */
  async moduleExists(name) {
    await this.refreshIndexIfUndefined();
    for (let i = 0; i < this._index.length; i++) {
      const elmt = this._index[i];
      if (elmt.hasOwnProperty("name") && elmt.name === name) {
        return true;
      }
    }
    return false;
  }

  /**
   * Get list of modules from registry with optional name and version
   * filtering.
   *
   * @param {string} filterName Module's name strict matching filtering
   * @param {string} filterVersion Semver range filtering
   * @returns {Promise<[{ name, version }]>}
   */
  async getModuleList(filterName = undefined, filterVersion = undefined) {
    await this.refreshIndexIfUndefined();
    let index = this._index;

    /* Filter by name */
    if (typeof filterName !== "undefined") {
      index = index.filter(elmt => {
        return elmt.hasOwnProperty("name") && elmt.name === filterName;
      });
    }

    /* Filter by semver version */
    if (filterVersion !== "undefined" && filterVersion !== "latest") {
      index = index.filter(elmt => {
        return semver.satisfies(semver.coerce(elmt.version), filterVersion);
      });
    }

    /* Order by descending version */
    index.sort((a, b) => {
      return semver.compare(b.version, a.version);
    });

    /* If "latest" requested, return the one with highest version */
    if (filterVersion === "latest" && index.length > 0) {
      index = index.slice(0, 1);
    }

    return index;
  }

  async getModuleVersionInfo(name, version) {
    const url = this.getURL();
    const infoUrl = [url, encodeURI(name), encodeURI(version)].join("/");

    const agent = new HTTPAgent();
    const response = await agent.fetch(infoUrl);
    if (!response.ok) {
      throw new AppRegistryError(`Could not get info from URL '${infoUrl}'`);
    }

    const data = await response.text();
    const info = JSON.parse(data);
    if (typeof info !== "object") {
      throw new AppRegistryError(
        `Malformed response from URL '${infoUrl}': ` + `${response.status} ${response.statusText}\n` + data
      );
    }
    return info;
  }
}

module.exports = {
  AppRegistry: AppRegistry,
  AppRegistryError: AppRegistryError
};
