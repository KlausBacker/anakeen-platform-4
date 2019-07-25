const fs = require("fs");
const path = require("path");
const fsUtils = require("../../plugins/files");

exports.writeTemplate = (installPath, argv) => {
  return new Promise((resolve, reject) => {
    const Name = argv.instanceName.charAt(0).toUpperCase() + argv.instanceName.slice(1).toLowerCase();
    const NAME = argv.instanceName.toUpperCase();
    const name = argv.instanceName.toLowerCase();
    if (!fs.existsSync(installPath)) {
      reject(`The path ${installPath} does not exist`);
    } else {
      fsUtils
        .writeTemplate(
          path.resolve(installPath, `510-${Name}WorkflowSettings.xml`),
          path.resolve(__dirname, "xml", "Settings.xml.mustache"),
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
