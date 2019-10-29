const path = require("path");
const os = require("os");

const XMLLoader = require(path.resolve(__dirname, "XMLLoader.js"));
const GenericError = require(path.resolve(__dirname, "GenericError.js"));
const Utils = require(path.resolve(__dirname, "Utils.js"));

class HTTPCredentialStoreError extends GenericError {}

class HTTPCredentialStore extends XMLLoader {
  constructor() {
    super();
    this.credentialFilename = ".anakeen-cli.credentials";
    this.defaultCredentialFilename = path.join(os.homedir(), this.credentialFilename);
    this.credentials = {};
  }

  /**
   * Load credentials to given file
   * @param {string} credentialStoreFile
   * @returns {Promise<HTTPCredentialStore>}
   */
  async loadCredentialStoreFile(credentialStoreFile) {
    try {
      if (await Utils.fileExists(credentialStoreFile)) {
        await this.loadFromFile(credentialStoreFile);
      } else {
        this.data = { credentials: { credential: [] } };
      }
    } catch (e) {
      throw new HTTPCredentialStoreError(e.message);
    }
    if (!this.data.hasOwnProperty("credentials")) {
      throw new HTTPCredentialStoreError(`Could not find /credentials node in '${credentialStoreFile}'`);
    }
    if (!(typeof this.data.credentials === "object")) {
      this.data.credentials = { credential: [] };
    }
    if (!this.data.credentials.hasOwnProperty("credential")) {
      throw new HTTPCredentialStoreError(`Could not find /credentials/credential node in '${credentialStoreFile}'`);
    }
    if (!(typeof this.data.credentials.credential === "object" && Array.isArray(this.data.credentials.credential))) {
      this.data.credentials.credential = [];
    }
    for (let i = 0; i < this.data.credentials.credential.length; i++) {
      const credentialNode = this.data.credentials.credential[i];
      if (!credentialNode.hasOwnProperty("$")) {
        throw new HTTPCredentialStoreError(`Could not get credential at element #${i}`);
      }
      ["url", "authUser", "authPassword"].forEach(propName => {
        if (!credentialNode.$.hasOwnProperty(propName)) {
          throw new HTTPCredentialStoreError(`Could not get property '${propName}' on element #${i}`);
        }
      });
      this.credentials[credentialNode.$.url] = {
        authUser: credentialNode.$.authUser,
        authPassword: credentialNode.$.authPassword
      };
    }
    return this;
  }

  /**
   * Save credentials to given file
   * @param {string} credentialStoreFile
   * @returns {Promise<HTTPCredentialStore>}
   */
  async saveCredentialStoreFile(credentialStoreFile) {
    this.data = { credentials: { credential: [] } };
    for (let url in this.credentials) {
      if (this.credentials.hasOwnProperty(url)) {
        this.data.credentials.credential.push({
          $: {
            url: url,
            authUser: this.credentials[url].authUser,
            authPassword: this.credentials[url].authPassword
          }
        });
      }
    }
    try {
      await this.saveToFile(credentialStoreFile);
    } catch (e) {
      throw new HTTPCredentialStoreError(e.message);
    }
    return this;
  }

  /**
   * Recursively find the location of a `.anakeen-cli.credentials` file
   * starting from the given directory up to the filesystem's root
   * @param {string} dir
   * @returns {Promise<null|*>} returns null if no file was found
   */
  async findCredentialLocationRecurse(dir) {
    let credentialFilename = path.join(dir, this.credentialFilename);
    if (await Utils.fileExists(credentialFilename)) {
      return credentialFilename;
    }
    const pathElmts = path.parse(dir);
    if (dir === pathElmts.root) {
      /* We have reached the filesystem's root and the file was not found */
      return null;
    }
    return await this.findCredentialLocationRecurse(path.resolve(dir, ".."));
  }

  /**
   * Find and return the location of the `.anakeen-cli.credentials` file
   * @returns {Promise<null|*>} returns null if no file was found
   */
  async findCredentialLocation() {
    let localLocation = path.join(process.cwd(), this.credentialFilename);
    if (await Utils.fileExists(localLocation)) {
      return localLocation;
    }
    let homeLocation = path.join(os.homedir(), this.credentialFilename);
    if (await Utils.fileExists(homeLocation)) {
      return homeLocation;
    }
    let credentialFilename = await this.findCredentialLocationRecurse(process.cwd());
    if (credentialFilename !== null) {
      return credentialFilename;
    }
    return null;
  }

  /**
   * Load credentials
   * @returns {Promise<HTTPCredentialStore>}
   */
  async loadCredentialStore() {
    let credentialStoreFile = await this.findCredentialLocation();
    if (credentialStoreFile === null) {
      credentialStoreFile = this.defaultCredentialFilename;
    }
    return await this.loadCredentialStoreFile(credentialStoreFile);
  }

  /**
   * Save credentials
   * @returns {Promise<HTTPCredentialStore>}
   */
  async saveCredentialStore() {
    let credentialStoreFile = await this.findCredentialLocation();
    if (credentialStoreFile === null) {
      credentialStoreFile = this.defaultCredentialFilename;
    }
    return await this.saveCredentialStoreFile(credentialStoreFile);
  }

  /**
   * Get the credential for the given URL
   * @param {string} url
   * @returns {null|*}
   */
  getCredentialForUrl(url) {
    url = Utils.normalizeUrl(url);
    for (let siteKey in this.credentials) {
      if (this.credentials.hasOwnProperty(siteKey)) {
        let credentialUrl = Utils.normalizeUrl(siteKey);
        if (url.startsWith(credentialUrl)) {
          return this.credentials[siteKey];
        }
      }
    }
    return null;
  }

  /**
   * Get credential from base site URL
   * @param {string} siteUrl
   * @returns {null|*}
   */
  getCredential(siteUrl) {
    if (this.credentials.hasOwnProperty(siteUrl)) {
      return this.credentials[siteUrl];
    }
    return null;
  }

  /**
   * Set credential for base site URL
   * @param {string} siteUrl
   * @param {string} authUser
   * @param {string} authPassword
   */
  setCredential(siteUrl, authUser, authPassword) {
    this.credentials[siteUrl] = { authUser, authPassword };
  }

  /**
   * Delete credential for base site URL
   * @param {string} siteUrl
   */
  deleteCredential(siteUrl) {
    if (this.credentials.hasOwnProperty(siteUrl)) {
      delete this.credentials[siteUrl];
    }
  }
}

module.exports = { HTTPCredentialStore, HTTPCredentialStoreError };
