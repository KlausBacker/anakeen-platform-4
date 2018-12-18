const fs = require("fs");
const path = require("path");
const fsUtils = require("../../plugins/files");

exports.writeTemplate = (installPath, argv) => {
  return new Promise((resolve, reject) => {
    const Name =
      argv.name.charAt(0).toUpperCase() + argv.name.slice(1).toLowerCase();
    const NAME = argv.name.toUpperCase();
    const name = argv.name.toLowerCase();
    const autocompletePath = path.join(installPath, `${Name}Autocompletion`);
    if (!fs.existsSync(installPath)) {
      reject(`The path ${installPath} does not exist`);
    } else {
      fs.mkdir(autocompletePath, err => {
        if (err) {
          reject(err);
        } else {
          const templateData = Object.assign({}, argv, {
            SSNAME: NAME,
            ssName: Name,
            ssname: name,
            WFL: argv.workflow ? argv.workflow.toUpperCase() : ""
          });
          fsUtils
            .writeTemplate(
              path.resolve(autocompletePath, `${Name}Autocompletion.php`),
              path.resolve(__dirname, "php", "Autocompletion.php.mustache"),
              templateData
            )
            .then(result => {
              resolve(result);
            })
            .catch(err => {
              reject(err);
            });
        }
      });
    }
  });
};
