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
const { exec } = require("child_process");

exec("git log -1 --date=iso --format=%cd --no-merges", (err, stdout, stderr) => {
  if (err) {
    console.error(err);
    console.error(stderr);
  } else {
    fs.readFile(parameterFilePath, function(err, data) {
      const document = libxml.parseXmlString(data);

      const versionTag = document.get("//sde:parameter[@name='VERSION']/sde:value", {
        sde: "https://platform.anakeen.com/4/schemas/sde/1.0"
      });

      const commitDateTag = document.get("//sde:parameter[@name='COMMIT_DATE']/sde:value", {
        sde: "https://platform.anakeen.com/4/schemas/sde/1.0"
      });
      if (versionTag) {
        versionTag.text(version);
      }

      if (commitDateTag) {
        commitDateTag.text(stdout.trim());
      }

      const comment = new libxml.Comment(document, "This file is auto generated. Do not manually update this file.");
      const configNode = document.root().get("//sde:config", { sde: "https://platform.anakeen.com/4/schemas/sde/1.0" });

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
  }
});
