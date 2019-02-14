const path = require("path");

const GenericError = require(path.resolve(__dirname, "GenericError.js"));
const XMLLoader = require(path.resolve(__dirname, "XMLLoader.js"));

class RepoXMLError extends GenericError {}

class RepoXML extends XMLLoader {
  constructor(filename) {
    super();
    this.filename = filename;
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
      throw new RepoXMLError(
        `Could not find /compose node in '${this.filename}'`
      );
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
   * @param {string} authUser
   * @param {string} authPassword
   * @returns {RepoXML}
   */
  addAppRegistry({ name, url, authUser, authPassword }) {
    if (this.getRegistryByName(name)) {
      throw new RepoXMLError(
        `Registry with name/identifier '${name}' already exists`
      );
    }

    let newRegistry = { $: { name, url } };
    if (authUser !== null) {
      newRegistry.$.authUser = authUser;
      if (authPassword !== null) {
        newRegistry.$.authPassword = authPassword;
      }
    }

    const registryList = this.getRegistryList();
    registryList.push(newRegistry);

    return this;
  }

  /**
   * @param {string} name Module's name
   * @param {string} version Module's semver version
   * @param {string} registry Module's registry name
   * @returns {RepoXML}
   */
  addModule({ name, version, registry }) {
    if (!this.getRegistryByName(registry)) {
      throw new RepoXMLError(`Registry '${registry}' does not exists`);
    }
    if (this.getModuleByName(name)) {
      throw new RepoXMLError(`Module '${name}' already exists in dependencies`);
    }

    let newModule = { $: { name, version, registry } };

    const moduleList = this.getModuleList();
    moduleList.push(newModule);

    return this;
  }

  /**
   * @returns {Array|*}
   */
  getModuleList() {
    return this.data.compose.dependencies[0].module;
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

    return undefined;
  }

  /**
   * @returns {Array|*}
   */
  getRegistryList() {
    return this.data.compose.registries[0].registry;
  }

  /**
   * @param {string} name
   * @returns {*}
   */
  getRegistryByName(name) {
    const registryList = this.getRegistryList();

    for (let i = 0; i < registryList.length; i++) {
      let registry = registryList[i];
      if (!registry.hasOwnProperty("$") || !registry.$.hasOwnProperty("name")) {
        throw new RepoXMLError(`Malformed registry at index #${i}`);
      }
      if (registry.$.name === name) {
        return registry.$;
      }
    }
    return undefined;
  }
}

module.exports = { RepoXML, RepoXMLError };
