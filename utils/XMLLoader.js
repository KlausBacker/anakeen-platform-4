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

  async loadFromFile(filename) {
    const content = await fs_readFile(filename, { encoding: "utf-8" });
    this.data = await xml2js_parseString(content, {
      tagNameProcessors: [xml2js.processors.stripPrefix]
    });
    return this;
  }

  async saveToFile(filename) {
    const builder = new xml2js.Builder();
    const xml = builder.buildObject(this.data);
    await fs_writeFile(filename, xml);
    return this;
  }
}

module.exports = XMLLoader;
