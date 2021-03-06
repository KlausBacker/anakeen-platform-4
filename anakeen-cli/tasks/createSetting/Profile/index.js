const fs = require("fs");
const path = require("path");
const fsUtils = require("../../plugins/files");

exports.writeTemplate = (installPath, argv, options) => {
  return new Promise((resolve, reject) => {
    if (!fs.existsSync(installPath)) {
      reject(`The path ${installPath} does not exist`);
    } else {
      fsUtils
        .writeTemplate(
          path.resolve(installPath, `120-Profile${argv.settingFileName}.xml`),
          path.resolve(__dirname, "xml", "Profile.xml"),
          Object.assign({}, argv, {
            nameLabel: argv.name.replace(/([A-Z])/g, "$1").trim(),
            NAME: argv.name.toUpperCase(),
            STRUCTURE_NAME: argv.associatedSmartStructure ? argv.associatedSmartStructure.toUpperCase() : "",
            SS: argv.associatedSmartStructure ? argv.associatedSmartStructure.toUpperCase() : ""
          }),
          options
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
