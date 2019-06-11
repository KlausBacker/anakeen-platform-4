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
          path.resolve(installPath, `${Name}Behavior.php`),
          path.resolve(__dirname, "php", "Behavior.php.mustache"),
          Object.assign({}, argv, {
            WFLNAME: NAME,
            wflName: Name,
            wflname: name,
            PARENTNAME: argv.parentName ? argv.parentName.toUpperCase() : "",
            Parentname: argv.parentName
              ? argv.parentName.charAt(0).toUpperCase() +
                argv.parentName.slice(1).toLowerCase()
              : ""
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
