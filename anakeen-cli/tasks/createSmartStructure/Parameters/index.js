const fs = require("fs");
const path = require("path");
const fsUtils = require("../../plugins/files");

exports.writeTemplate = (installPath, argv) => {
  return new Promise((resolve, reject) => {
    const Name = argv.name.charAt(0).toUpperCase() + argv.name.slice(1).toLowerCase();
    const NAME = argv.name.toUpperCase();
    const name = argv.name.toLowerCase();
    if (!fs.existsSync(installPath)) {
      reject(`The path ${installPath} does not exist`);
    } else {
      fsUtils
        .writeTemplate(
          path.resolve(installPath, `110-${Name}Parameters.xml`),
          path.resolve(__dirname, "xml", "Parameters.xml"),
          Object.assign({}, argv, {
            SSNAME: NAME,
            ssName: Name,
            ssname: name
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