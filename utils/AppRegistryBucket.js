const path = require("path");
const semver = require("semver");

const GenericError = require(path.resolve(__dirname, "GenericError.js"));
const { HTTPAgent } = require(path.resolve(__dirname, "HTTPAgent.js"));

class AppRegistryBucketError extends GenericError {}

class AppRegistryBucket {
  /**
   * @param {string} url Registry's base URL
   * @param {string} bucket Registry's bucket name
   * @param {string} authUser (optional) HTTP auth username
   * @param {string} authPassword (optional) HTTP auth password
   */
  constructor({ url, bucket, authUser, authPassword }) {
    this._index = undefined;
    this.url = url;
    this.bucket = bucket;
    /* TODO: Implement HTTP Basic authentication. */
    this.authUser = authUser;
    this.authPassword = authPassword;
  }

  /**
   * @returns {Promise<AppRegistryBucket>}
   */
  async refreshIndex() {
    const bucketUrl = this.getBucketURL();
    const agent = new HTTPAgent();
    const response = await agent.fetch(bucketUrl);
    if (!response.ok) {
      throw new AppRegistryBucketError(
        `Could not get content from registry at URL '${bucketUrl}'`
      );
    }
    const data = await response.text();
    const index = JSON.parse(data);
    if (!Array.isArray(index)) {
      throw new AppRegistryBucketError(
        `Malformed response from registry at URL '${bucketUrl}': ` +
          `${response.status} ${response.statusText}\n` +
          data
      );
    }
    for (let i = 0; i < index.length; i++) {
      index[i].url = [
        bucketUrl,
        encodeURI(index[i].name),
        encodeURI(index[i].version)
      ].join("/");
    }
    this._index = index;
    return this;
  }

  /**
   * @returns {Promise<AppRegistryBucket>}
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
  getBucketURL() {
    return `${this.url}/${encodeURI(this.bucket)}`;
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
   * Get list of modules from registry's bucket with optional name and version
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

    /* Order by descending order */
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
    const bucketUrl = this.getBucketURL();
    const infoUrl = [bucketUrl, encodeURI(name), encodeURI(version)].join("/");

    const agent = new HTTPAgent();
    const response = await agent.fetch(infoUrl);
    if (!response.ok) {
      throw new AppRegistryBucketError(
        `Could not get info from URL '${infoUrl}'`
      );
    }

    const data = await response.text();
    const info = JSON.parse(data);
    if (typeof info !== "object") {
      throw new AppRegistryBucketError(
        `Malformed response from URL '${infoUrl}': ` +
          `${response.status} ${response.statusText}\n` +
          data
      );
    }
    return info;
  }
}

module.exports = { AppRegistryBucket, AppRegistryBucketError };
