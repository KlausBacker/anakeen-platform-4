const path = require("path");
const util = require("util");
const fs = require("fs");
const SHA256Digest = require(path.resolve(__dirname, "SHA256Digest"));

const fs_access = util.promisify(fs.access);

const GenericError = require(path.resolve(__dirname, "GenericError.js"));
const XMLLoader = require(path.resolve(__dirname, "XMLLoader.js"));

class RepoLockXMLError extends GenericError {}

class RepoLockXML extends XMLLoader {
  constructor(filename) {
    super();
    this.filename = filename;
  }

  static repoLockXMLTemplate() {
    return {
      "compose-lock": {
        $: {
          xmlns: "https://platform.anakeen.com/4/schemas/compose-lock/1.0"
        },
        module: []
      }
    };
  }

  /**
   * @param {string} filename
   * @returns {Promise<RepoLockXML>}
   */
  async loadFromFile(filename) {
    let lockFileExists = true;
    try {
      await fs_access(filename);
    } catch (e) {
      lockFileExists = false;
    }
    if (!lockFileExists) {
      this.setData(RepoLockXML.repoLockXMLTemplate());
    } else {
      await super.loadFromFile(filename);
    }
    this.checkStructure();
    return this;
  }

  async load() {
    await this.loadFromFile(this.filename);
    return this;
  }

  async save() {
    return await this.saveToFile(this.filename);
  }

  checkStructure() {
    if (!this.data.hasOwnProperty("compose-lock")) {
      throw new RepoLockXMLError(`Could not find /compose-lock node in '${this.filename}'`);
    }
    if (!this.data["compose-lock"].hasOwnProperty("$")) {
      throw new RepoLockXMLError(`/compose-lock is not a valid root node`);
    }
    if (!this.data["compose-lock"].hasOwnProperty("module")) {
      this.data["compose-lock"].module = [];
    }
    return this;
  }

  /**
   * @param {string} name
   * @param {boolean} throwErrorIfNotFound (default: true)
   * @returns {RepoLockXML}
   */
  deleteModuleByName(name, throwErrorIfNotFound = true) {
    const moduleList = this.getModuleList();
    let foundIndex = -1;
    for (let i = 0; i < moduleList.length; i++) {
      let module = moduleList[i];
      if (module.hasOwnProperty("$") && module.$.hasOwnProperty("name") && module.$.name === name) {
        foundIndex = i;
        break;
      }
    }

    if (foundIndex < 0 && throwErrorIfNotFound) {
      throw new RepoLockXMLError(`Module '${name}' not found in locked dependencies`);
    }
    if (foundIndex >= 0) {
      moduleList.splice(foundIndex, 1);
    }

    return this;
  }

  /**
   * @param {string} name
   * @param {string} version
   * @param {[{ type, src, sha256 }]} resources
   */
  updateModule({ name, version, resources }) {
    this.deleteModuleByName(name, true);
    this.addModule({ name, version, resources });
  }

  /**
   * @param {string} name
   * @param {string} version
   * @param {[{ type, src, sha256 }]} resources
   */
  addOrUpdateModule({ name, version, resources }) {
    this.deleteModuleByName(name, false);
    this.addModule({ name, version, resources });
  }

  /**
   * @param {string} name Module's name
   * @param {string} version Module's semver version
   * @param {string} src URL from which the module has been downloaded
   * @param {[{ type, src, sha256, pathname }]} resources
   * @returns {RepoLockXML}
   */
  addModule({ name, version, resources }) {
    if (this.getModuleByName(name)) {
      throw new RepoLockXMLError(`Module '${name}' already exists in locked dependencies`);
    }

    let newModule = {
      $: {
        name,
        version
      },
      resources: {
        app: [],
        src: []
      }
    };
    for (let i = 0; i < resources.length; i++) {
      const r = resources[i];
      newModule.resources[r.type].push({
        $: {
          src: r.src,
          sha256: r.sha256,
          path: r.pathname
        }
      });
    }

    const moduleList = this.getModuleList();
    moduleList.push(newModule);

    return this;
  }

  async checkIfModuleIsValid({ name, appPath, srcPath }) {
    const currentModule = this.getModuleByName(name);
    const appGood = await currentModule.resources.app.reduce(async (acc, currentApp) => {
      //if one of the ressource is false, all the module is invalid
      const previousResult = await acc;
      if (previousResult === false) {
        return acc;
      }
      const sha = await SHA256Digest(path.join(appPath, currentApp.$.path));
      return sha === currentApp.$.sha256;
    }, Promise.resolve(true));

    if (appGood === false) {
      return false;
    }
    return await currentModule.resources.src.reduce(async (acc, currentSrc) => {
      //if one of the ressource is false, all the module is invalid
      const previousResult = await acc;
      if (previousResult === false) {
        return acc;
      }
      const sha = await SHA256Digest(path.join(srcPath, currentSrc.$.path));
      return sha === currentSrc.$.sha256;
    }, Promise.resolve(true));
  }

  /**
   * @returns {Array|*}
   */
  getModuleList() {
    return this.data["compose-lock"].module;
  }

  swipeModuleList() {
    this.data["compose-lock"].module = [];
  }
  /**
   * @param {string} name
   * @returns {*}|undefined
   */
  getModuleByName(name) {
    const moduleList = this.getModuleList();

    for (let i = 0; i < moduleList.length; i++) {
      let module = moduleList[i];
      if (!module.hasOwnProperty("$") || !module.$.hasOwnProperty("name")) {
        throw new RepoLockXMLError(`Malformed module at index #${i}`);
      }
      if (module.$.name === name) {
        return module;
      }
    }

    return undefined;
  }
}

module.exports = { RepoLockXML, RepoLockXMLError };
