const path = require("path");

const GenericError = require(path.resolve(__dirname, "GenericError.js"));
const XMLLoader = require(path.resolve(__dirname, "XMLLoader.js"));
const Utils = require(path.resolve(__dirname, "Utils.js"));
const { AppRegistry } = require(path.resolve(__dirname, "AppRegistry.js"));
const { checkFile } = require("@anakeen/anakeen-module-validation");

class RepoXMLError extends GenericError {}

class RepoXMLRegistryNotFoundError extends RepoXMLError {}

class RepoXMLModuleNotFoundError extends RepoXMLError {}

class RepoXML extends XMLLoader {
  constructor(filename, credentialStore) {
    super();
    this.filename = filename;
    this.credentialStore = credentialStore;
  }

  /**
   * @param {string} filename
   * @returns {Promise<RepoXML>}
   */
  async loadFromFile(filename) {
    await super.loadFromFile(filename);
    const check = checkFile(filename);
    if (!check.ok) {
      throw new RepoXMLError(`The repo file is not valid ${filename} : ${check.error}`);
    }
    return this;
  }

  async load() {
    await this.loadFromFile(this.filename);
    return this;
  }

  async save() {
    this._orderElement();
    return await this.saveToFile(this.filename);
  }

  getConfigLocalRepo() {
    return this.data.compose.config[0].localRepo[0].$.path;
  }

  getConfigLocalSrc() {
    return this.data.compose.config[0].localSrc[0].$.path;
  }

  addAppLocalPath({ localPath }) {
    this.data.compose.config[0].localApp = [
      {
        $: {
          path: localPath
        }
      }
    ];
  }

  getAppLocalPath() {
    const app = this.data.compose.config[0].localApp || [];
    return app.map(currentApp => {
      return currentApp.$.path;
    });
  }

  /**
   * @param {string} name
   * @param {string} url
   * @returns {RepoXML}
   */
  addAppRegistry({ name, url }) {
    url = Utils.normalizeUrl(url);

    let newRegistry = { $: { name, url } };

    const registryList = this._getRegistryList();

    const registries = registryList.reduce(
      (acc, currentRegistry) => {
        if (currentRegistry.$.name !== name) {
          acc.push(currentRegistry);
        }
        return acc;
      },
      [newRegistry]
    );

    const orderedRegistries = registries.sort((moduleA, moduleB) => {
      if (moduleA.name < moduleB.name) return -1;
      if (moduleA.name > moduleB.name) return 1;
      return 0;
    });

    this._setRegistryList(orderedRegistries);

    return this;
  }

  /**
   * @param {string} name Module's name
   * @param {string} version Module's semver version
   * @param {string} registry Module's registry name
   * @returns {RepoXML}
   */
  addModule({ name, version, registry }) {
    if (!this.registryExists(registry)) {
      throw new RepoXMLError(`Registry '${registry}' does not exists`);
    }

    let newModule = { $: { name, version, registry } };

    const moduleList = this.getModuleList();
    const newList = moduleList.reduce(
      (acc, currentModule) => {
        if (currentModule.$.name !== name) {
          acc.push(currentModule);
        }
        return acc;
      },
      [newModule]
    );

    const orderedList = newList.sort((moduleA, moduleB) => {
      if (moduleA.name < moduleB.name) return -1;
      if (moduleA.name > moduleB.name) return 1;
      return 0;
    });

    this._setModuleList(orderedList);

    return this;
  }

  /**
   * Updates a module in repo.xml
   * @param name the module's name
   * @param version the module's version
   * @param registry the module's registry
   */
  updateModule({ name, version, registry }) {
    if (!this.moduleExists(name)) {
      throw new RepoXMLError(`Module '${name}' does not exist`);
    }
    if (!this.registryExists(registry)) {
      throw new RepoXMLError(`Registry '${registry}' does not exists`);
    }
    const appRegistry = this.getRegistryByName(registry);
    if (!appRegistry.getModuleVersionInfo(name, version)) {
      throw new RepoXMLError(`Version '${version}' of module '${name}' does not exists`);
    }

    const moduleList = this.getModuleList();

    for (let i = 0; i < moduleList.length; i++) {
      const module = moduleList[i];
      if (module.$.name === name) {
        moduleList[i] = { $: { name, version, registry } };
      }
    }
    return this;
  }

  /**
   * @returns {Array|*}
   */
  getModuleList() {
    return this.data.compose.dependencies[0].module || [];
  }

  /**
   * Check if module exists in registry
   * @param {string} name Module's name
   * @returns {boolean}
   */
  moduleExists(name) {
    try {
      this.getModuleByName(name);
    } catch (e) {
      if (e instanceof RepoXMLModuleNotFoundError) {
        return false;
      }
      throw e;
    }
    return true;
  }

  /**
   * @param {string} name
   * @returns {*}
   */
  getModuleByName(name) {
    const moduleList = this.getModuleList();

    for (let i = 0; i < moduleList.length; i++) {
      let module = moduleList[i];
      if (!module.hasOwnProperty("$") || !module.$.hasOwnProperty("name")) {
        throw new RepoXMLError(`Malformed module at index #${i}`);
      }
      if (module.$.name === name) {
        return module.$;
      }
    }
    throw new RepoXMLModuleNotFoundError(`Found no module with name '${name} in 'repo.xml'`);
  }

  /**
   * @returns {Array|*}
   */
  _getRegistryList() {
    return this.data.compose.registries[0].registry || [];
  }

  _setRegistryList(list) {
    if (!this.data.compose.registries[0].registry) {
      this.data.compose.registries[0] = { registry: [] };
    }
    this.data.compose.registries[0].registry = list;
  }

  _setModuleList(list) {
    if (!this.data.compose.dependencies[0].module) {
      this.data.compose.dependencies[0] = { module: [] };
    }
    this.data.compose.dependencies[0].module = list;
  }

  getRegistryList() {
    if (!this.data.compose.registries[0]) {
      return [];
    }
    return this.data.compose.registries[0].registry.map(currentRegistry => {
      return {
        name: currentRegistry.$.name,
        url: currentRegistry.$.url
      };
    });
  }

  /**
   * Check if a registry exists in 'repo.xml'
   * @param {string} registryName Registry's name
   * @returns {boolean}
   */
  registryExists(registryName) {
    try {
      this.getRegistryByName(registryName);
    } catch (e) {
      if (e instanceof RepoXMLRegistryNotFoundError) {
        return false;
      }
      throw e;
    }
    return true;
  }

  /**
   * @param {string} name
   * @returns {AppRegistry}
   */
  getRegistryByName(name) {
    const registryList = this._getRegistryList();

    for (let i = 0; i < registryList.length; i++) {
      let registry = registryList[i];
      if (!registry.hasOwnProperty("$") || !registry.$.hasOwnProperty("name")) {
        throw new RepoXMLError(`Malformed registry at index #${i}`);
      }
      if (registry.$.name === name) {
        return new AppRegistry({ ...registry.$, ...{ credentialStore: this.credentialStore } });
      }
    }
    throw new RepoXMLRegistryNotFoundError(`Registry with name '${name}' not found in 'repo.xml'`);
  }

  _orderElement() {
    const module = this.getModuleList();
    if (module && module.length) {
      const orderedList = module.sort((moduleA, moduleB) => {
        if (moduleA.name < moduleB.name) return -1;
        if (moduleA.name > moduleB.name) return 1;
        return 0;
      });

      this._setModuleList(orderedList);
    }
  }
}

module.exports = {
  RepoXML,
  RepoXMLError,
  RepoXMLRegistryNotFoundError,
  RepoXMLModuleNotFoundError
};
