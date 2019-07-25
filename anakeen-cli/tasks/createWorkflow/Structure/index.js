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
      const templateData = Object.assign({}, argv, {
        WFLNAME: NAME,
        wflName: Name,
        wflname: name,
        PARENTNAME: argv.parentName ? argv.parentName.toUpperCase() : ""
      });
      fsUtils
        .writeTemplates(
          {
            destinationPath: path.resolve(installPath, `100-${Name}Structure.xml`),
            templateFile: path.resolve(__dirname, "xml", "Structure.xml.mustache"),
            templateData
          },
          {
            destinationPath: path.resolve(installPath, `${Name}Graph.xml`),
            templateFile: path.resolve(__dirname, "xml", "Graph.xml.mustache"),
            templateData
          }
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
