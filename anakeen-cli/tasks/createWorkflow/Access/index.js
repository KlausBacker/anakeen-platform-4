const fs = require("fs");
const path = require("path");
const fsUtils = require("../../plugins/files");
const camelCase = require("camelcase");

exports.writeTemplate = (installPath, argv) => {
  return new Promise((resolve, reject) => {
    const Name = camelCase(argv.instanceName, { pascalCase: true });
    const NAME = argv.instanceName.toUpperCase();
    const name = argv.instanceName.toLowerCase();
    if (!fs.existsSync(installPath)) {
      reject(`The path ${installPath} does not exist`);
    } else {
      fsUtils
        .writeTemplate(
          path.resolve(installPath, `140-${Name}WorkflowAccess.xml`),
          path.resolve(__dirname, "xml", "Access.xml.mustache"),
          Object.assign({}, argv, {
            WFLNAME: NAME,
            wflName: Name,
            wflname: name,
            SSNAME: argv.associatedSmartStructure.toUpperCase(),
            MODELNAME: argv.modelName.toUpperCase()
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
