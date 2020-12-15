/**
 * Copy package version to parameters.xml
 * @type {module:fs}
 */
const fs = require("fs");
const libxml = require("libxmljs");
const xmlFormat = require("xml-formatter");

const version = require("../package.json").ap4version;
const parameterFilePath = __dirname + "/parametersTemplate.xml";
const outputParameterFilePath = __dirname + "/../src/config/SmartDataEngine/About/parameters.xml";
const ns = "https://platform.anakeen.com/4/schemas/sde/1.0";

fs.readFile(parameterFilePath, function(err, data) {
  const document = libxml.parseXmlString(data);

  const versionTag = document.get("//sde:parameter[@name='VERSION']/sde:value", {
    sde: ns
  });

  if (versionTag) {
    versionTag.text(version);
  }

  const comment = new libxml.Comment(document, "This file is auto generated. Do not manually update this file.");
  const configNode = document.root().get("//sde:config", { sde: ns });

  configNode.addChild(comment);

  const xmlOutput = xmlFormat(document.toString(), {
    indentation: "  ",
    collapseContent: true,
    lineSeparator: "\n"
  });

  fs.writeFile(outputParameterFilePath, xmlOutput, function(err) {
    if (err) {
      throw new Error(err);
    }
    // eslint-disable-next-line
        console.log(`Write file "${outputParameterFilePath}" Version ${version}`);
  });
});
