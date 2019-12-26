const path = require("path");
const semver = require("semver");

const GenericError = require(path.resolve(__dirname, "GenericError.js"));
const { HTTPAgent } = require(path.resolve(__dirname, "HTTPAgent.js"));

class AppRegistryError extends GenericError {}

class AppRegistry {
  /**
   * @param {string} url Registry's base URL
   */
  constructor({ url, credentialStore }) {
    this._index = undefined;
    this.url = url.trim().replace(/\/+$/, "");
    this.agent = new HTTPAgent({
      credentialStore
    });
  }

  /**
   * @returns {Promise<AppRegistry>}
   */
  async refreshIndex() {
    const url = this.getURL();
    const response = await this.agent.fetch(url);
    if (!response.ok) {
      throw new AppRegistryError(
        `Could not get content from registry at URL '${url}' (HTTP ${response.status} ${response.statusText})`
      );
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
    const index = await this.refreshIndex();
    return !!index;
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
        return semver.satisfies(elmt.version, filterVersion);
      });
    }

    /* If "latest" requested, return the one with highest version not in RC mode */
    if (filterVersion === "latest" && index.length > 0) {
      //Suppress pre release from analysis
      index = index.filter(currentIndex => {
        return !semver.prerelease(currentIndex.version);
      });
      //Get the greater index
      return [
        index.reduce(
          (acc, currentIndex) => {
            if (semver.gt(acc.version, currentIndex.version)) {
              return acc;
            }
            return currentIndex;
          },
          { version: "0.0.1", name: undefined }
        )
      ];
    }

    return index;
  }

  async getModuleVersionInfo(name, version) {
    const url = this.getURL();
    const infoUrl = [url, encodeURI(name), encodeURI(version)].join("/");

    const response = await this.agent.fetch(infoUrl);
    if (!response.ok) {
      throw new AppRegistryError(
        `Could not get info from URL '${infoUrl}' (HTTP ${response.status} ${response.statusText})`
      );
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
