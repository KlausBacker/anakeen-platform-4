const fs = require("fs");
const path = require("path");
const fsUtils = require("../../plugins/files");
const camelCase = require("camelcase");

exports.writeTemplate = (installPath, argv) => {
  return new Promise((resolve, reject) => {
    const Name = camelCase(argv.name, { pascalCase: true });

    // a suppr
    // const Name = argv.name.charAt(0).toUpperCase() + argv.name.slice(1).toLowerCase();
    const NAME = argv.name.toUpperCase();
    const name = argv.name.toLowerCase();
    const Parentname = argv.parentName
      ? argv.parentName.charAt(0).toUpperCase() + argv.parentName.slice(1).toLowerCase()
      : undefined;
    if (!fs.existsSync(installPath)) {
      reject(`The path ${installPath} does not exist`);
    } else {
      fsUtils
        .writeTemplate(
          path.resolve(installPath, `${Name}Behavior.php`),
          path.resolve(__dirname, "php", "Behavior.php.mustache"),
          Object.assign({}, argv, {
            SSNAME: NAME,
            ssName: Name,
            ssname: name,
            Parentname
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
