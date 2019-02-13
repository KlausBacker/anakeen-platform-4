const path = require("path");
const util = require("util");
const fs = require("fs");

const fs_access = util.promisify(fs.access);

const GenericError = require(path.resolve(__dirname, "GenericError.js"));
const XMLLoader = require(path.resolve(__dirname, "XMLLoader.js"));

class RepoContentXMLError extends GenericError {}

class RepoContentXML extends XMLLoader {
  constructor(filename) {
    super();
    this.filename = filename;
  }

  static repoContentXMLTemplate() {
    return {
      repo: {
        $: {
          xmlns: "https://platform.anakeen.com/4/schemas/compose-lock/1.0",
          format: "control",
          label: "",
          status: ""
        },
        modules: []
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
      this.setData(RepoContentXML.repoContentXMLTemplate());
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
      this.data["repo"].modules = [];
    }
    return this;
  }
}

module.exports = { RepoContentXML, RepoContentXMLError };
