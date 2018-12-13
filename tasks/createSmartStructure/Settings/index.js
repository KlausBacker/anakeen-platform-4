const fs = require("fs");
const path = require("path");
const fsUtils = require("../../plugins/files");

exports.writeTemplate = (installPath, argv) => {
  return new Promise((resolve, reject) => {
    const Name =
      argv.name.charAt(0).toUpperCase() + argv.name.slice(1).toLowerCase();
    const NAME = argv.name.toUpperCase();
    const name = argv.name.toLowerCase();
    if (!fs.existsSync(installPath)) {
      reject(`The path ${installPath} does not exist`);
    } else {
      fsUtils
        .writeTemplate(
          path.resolve(installPath, `500-${Name}Settings.xml`),
          path.resolve(__dirname, "xml", "Settings.xml"),
          Object.assign({}, argv, {
            SSNAME: NAME,
            ssName: Name,
            ssname: name,
            WFL: argv.workflow ? argv.workflow.toUpperCase() : ""
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
