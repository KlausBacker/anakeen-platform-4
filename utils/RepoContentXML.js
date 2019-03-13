const path = require("path");
const util = require("util");
const fs = require("fs");

const fs_access = util.promisify(fs.access);

const GenericError = require(path.resolve(__dirname, "GenericError.js"));
const XMLLoader = require(path.resolve(__dirname, "XMLLoader.js"));
const { AppModuleFile } = require(path.resolve(__dirname, "AppModuleFile.js"));

class RepoContentXMLError extends GenericError {}

class RepoContentXML extends XMLLoader {
  constructor(filename, options = {}) {
    super();
    this.options = typeof options === "object" ? options : {};
    this.filename = filename;
  }

  repoContentXMLTemplate() {
    const format = this.options.hasOwnProperty("format")
      ? this.options.format
      : "control";
    const label = this.options.hasOwnProperty("label")
      ? this.options.label
      : "";
    const status = this.options.hasOwnProperty("status")
      ? this.options.status
      : "";
    return {
      repo: {
        $: { format, label, status },
        modules: [
          {
            module: []
          }
        ]
      }
    };
  }

  async load() {
    return await this.loadFromFile(this.filename);
  }

  async save() {
    return await this.saveToFile(this.filename);
  }

  async loadFromFile(filename) {
    let lockFileExists = true;
    try {
      await fs_access(filename);
    } catch (e) {
      lockFileExists = false;
    }
    if (!lockFileExists) {
      this.setData(this.repoContentXMLTemplate());
    } else {
      await super.loadFromFile(filename);
    }
    this.checkStructure();
    return this;
  }

  checkStructure() {
    if (!this.data.hasOwnProperty("repo")) {
      throw new RepoContentXMLError(
        `Could not find /compose-lock node in '${this.filename}'`
      );
    }
    if (!this.data["repo"].hasOwnProperty("$")) {
      throw new RepoContentXMLError(`/compose-lock is not a valid root node`);
    }
    if (!this.data["repo"].hasOwnProperty("modules")) {
      this.data["repo"].modules = [
        {
          module: []
        }
      ];
    }
    return this;
  }

  reset() {
    this.setData(this.repoContentXMLTemplate());
    this.checkStructure();
    return this;
  }

  async addModuleFile(file) {
    const appModuleFile = new AppModuleFile(file);
    const xmlStr = await appModuleFile.getInfoXMLText();

    const xmlLoader = new XMLLoader();
    await xmlLoader.loadFromString(xmlStr);

    const rootNode = xmlLoader.data.module;
    /* Remove xmlns declaration and unused child nodes */
    delete rootNode.$.xmlns;
    for (let prop of Object.keys(rootNode)) {
      if (!["$", "description", "requires"].includes(prop)) {
        delete rootNode[prop];
      }
    }

    this.data.repo.modules[0].module.push(rootNode);

    return this;
  }
}

module.exports = { RepoContentXML, RepoContentXMLError };
