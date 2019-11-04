const fs = require("fs");
const path = require("path");
const fsUtils = require("../../plugins/files");

exports.writeTemplate = (installPath, argv) => {
  return new Promise((resolve, reject) => {
    if (!fs.existsSync(installPath)) {
      reject(`The path ${installPath} does not exist`);
    } else {
      fsUtils
        .writeTemplate(
          path.resolve(installPath, `260-Timer${argv.name}.xml`),
          path.resolve(__dirname, "xml", "Timer.xml"),
          Object.assign({}, argv, {
            nameLabel: argv.name.replace(/([A-Z])/g, "$1").trim(),
            NAME: argv.name.toUpperCase(),
            SS: argv.associatedSmartStructure ? argv.associatedSmartStructure.toUpperCase() : "",
            WFL: argv.associatedWorkflow ? argv.associatedWorkflow.toUpperCase() : ""
          })
        )
        .then(result => {
          resolve(result);
        })
        .catch(err => {
          reject(err);
        });
    }
  });
};
