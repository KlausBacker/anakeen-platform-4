const xml2js = require("xml2js");
const util = require("util");
const fs = require("fs");

const fs_writeFile = util.promisify(fs.writeFile);
const fs_readFile = util.promisify(fs.readFile);
const xml2js_parseString = util.promisify(xml2js.parseString);

class XMLLoader {
  constructor() {
    this.data = {};
  }

  setData(data) {
    this.data = data;
    return this;
  }

  async loadFromString(xml) {
    this.data = await xml2js_parseString(xml, {
      tagNameProcessors: [xml2js.processors.stripPrefix]
    });
    return this;
  }

  async loadFromFile(filename) {
    const content = await fs_readFile(filename, { encoding: "utf-8" });
    return this.loadFromString(content);
  }

  async toString() {
    const builder = new xml2js.Builder();
    return builder.buildObject(this.data);
  }

  async saveToFile(filename) {
    await fs_writeFile(filename, this.toString());
    return this;
  }
}

module.exports = XMLLoader;
