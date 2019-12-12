const path = require("path");

const GenericError = require(path.resolve(__dirname, "GenericError.js"));
const XMLLoader = require(path.resolve(__dirname, "XMLLoader.js"));
const Utils = require(path.resolve(__dirname, "Utils.js"));
const { AppRegistry } = require(path.resolve(__dirname, "AppRegistry.js"));

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
    if (!this.data.hasOwnProperty("compose")) {
      throw new RepoXMLError(`Could not find /compose node in '${this.filename}'`);
    }
    if (!this.data.compose.hasOwnProperty("registries")) {
      this.data.compose.registries = [];
    }
    if (!Array.isArray(this.data.compose.registries)) {
      throw new RepoXMLError(`/compose/registries is not an array...`);
    }
    if (typeof this.data.compose.registries[0] !== "object") {
      this.data.compose.registries[0] = { registry: [] };
    } else if (!this.data.compose.registries[0].hasOwnProperty("registry")) {
      throw new RepoXMLError(`Malformed /compose/registries node`);
    }
    if (typeof this.data.compose.dependencies[0] !== "object") {
      this.data.compose.dependencies[0] = { module: [] };
    } else if (!this.data.compose.dependencies[0].hasOwnProperty("module")) {
      throw new RepoXMLError(`Malformed /compose/dependencies node`);
    }
    return this;
  }

  getConfigLocalRepo() {
    return this.data.compose.config[0].localRepo[0].$.path;
  }

  getConfigLocalSrc() {
    return this.data.compose.config[0].localSrc[0].$.path;
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
        acc.push(currentRegistry);
        return acc;
      },
      [newRegistry]
    );

    this._setRegistryList(registries);

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
    if (this.moduleExists(name)) {
      throw new RepoXMLError(`Module '${name}' already exists in dependencies`);
    }

    let newModule = { $: { name, version, registry } };

    const moduleList = this.getModuleList();
    moduleList.push(newModule);

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
    return this.data.compose.dependencies[0].module;
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
    return this.data.compose.registries[0].registry;
  }

  _setRegistryList(list) {
    this.data.compose.registries[0].registry = list;
  }

  getRegistryList() {
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
}

module.exports = {
  RepoXML,
  RepoXMLError,
  RepoXMLRegistryNotFoundError,
  RepoXMLModuleNotFoundError
};
