const fs = require("fs");
const path = require("path");
const fsUtils = require("../../plugins/files");
const camelCase = require("camelcase");

exports.writeTemplate = (installPath, argv) => {
  let namePascalCase = "";
  if (argv.associatedSmartStructure) {
    namePascalCase = camelCase(argv.associatedSmartStructure, { pascalCase: true });
  }
  return new Promise((resolve, reject) => {
    if (!fs.existsSync(installPath)) {
      reject(`The path ${installPath} does not exist`);
    } else {
      fsUtils
        .writeTemplate(
          path.resolve(installPath, `230-FieldAccess${namePascalCase}.xml`),
          path.resolve(__dirname, "xml", "FieldAccess.xml"),
          Object.assign({}, argv, {
            nameLabel: argv.name.replace(/([A-Z])/g, "$1").trim(),
            NAME: argv.name.toUpperCase(),
            SS: argv.associatedSmartStructure ? argv.associatedSmartStructure.toUpperCase() : ""
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
