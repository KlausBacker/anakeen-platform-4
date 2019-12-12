const path = require("path");
const fs = require("fs");

const XMLLoader = require(path.resolve(__dirname, "XMLLoader.js"));
const GenericError = require(path.resolve(__dirname, "GenericError.js"));
const Utils = require(path.resolve(__dirname, "Utils.js"));
const { checkFile } = require("@anakeen/anakeen-module-validation");

class HTTPCredentialStoreError extends GenericError {}

const CREDENTIAL_NAME = ".anakeen-cli.credentials.xml";

class HTTPCredentialStore extends XMLLoader {
  constructor(currentPath) {
    super();
    const checkIfFileExist = file => {
      if (!fs.existsSync(path.dirname(file))) {
        return false;
      }
      if (fs.existsSync(file)) {
        return file;
      }
      if (
        path.resolve(path.dirname(file), "..", CREDENTIAL_NAME) === path.resolve(path.dirname(file), CREDENTIAL_NAME)
      ) {
        return false;
      }
      return checkIfFileExist(path.resolve(path.dirname(file), "..", CREDENTIAL_NAME));
    };

    this.cwd = currentPath;
    this.credentialStoreFile = checkIfFileExist(path.join(this.cwd, CREDENTIAL_NAME));
    this.credentials = {};
  }

  /**
   * Load credentials to given file
   * @returns {Promise<HTTPCredentialStore>}
   */
  async loadCredentialStore() {
    try {
      if (await Utils.fileExists(this.credentialStoreFile)) {
        const checkCredential = checkFile(this.credentialStoreFile);
        if (checkCredential.error) {
          // noinspection ExceptionCaughtLocallyJS
          throw new HTTPCredentialStoreError(checkCredential.error);
        }
        await this.loadFromFile(this.credentialStoreFile);
      } else {
        this.data = { credentials: { credential: [] } };
      }
    } catch (e) {
      throw new HTTPCredentialStoreError(e.message);
    }
    if (!(typeof this.data.credentials === "object")) {
      this.data.credentials = { credential: [] };
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
   * @returns {Promise<HTTPCredentialStore>}
   */
  async saveCredentialStore() {
    this.data = {
      credentials: {
        $: {
          xmlns: "https://platform.anakeen.com/4/schemas/compose-credentials/1.0"
        },
        credential: []
      }
    };
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
    if (!this.credentialStoreFile) {
      this.credentialStoreFile = path.join(this.cwd, CREDENTIAL_NAME);
    }
    try {
      await this.saveToFile(this.credentialStoreFile);
    } catch (e) {
      throw new HTTPCredentialStoreError(e.message);
    }
    return this;
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
