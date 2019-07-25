const fs = require("fs");
const path = require("path");
const fsUtils = require("../../plugins/files");

exports.writeTemplate = (installPath, argv) => {
  return new Promise((resolve, reject) => {
    const renderPath = path.join(installPath, "Render");
    const Name = argv.name.charAt(0).toUpperCase() + argv.name.slice(1).toLowerCase();
    const NAME = argv.name.toUpperCase();
    const name = argv.name.toLowerCase();
    if (!fs.existsSync(installPath)) {
      reject(`The path ${installPath} does not exist`);
    } else {
      fs.mkdir(renderPath, err => {
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
            .writeTemplates(
              {
                destinationPath: path.resolve(renderPath, `${Name}Access.php`),
                templateFile: path.resolve(__dirname, "php", "Access.php.mustache"),
                templateData
              },
              {
                destinationPath: path.resolve(renderPath, `${Name}EditRender.php`),
                templateFile: path.resolve(__dirname, "php", "Edit.php.mustache"),
                templateData
              },
              {
                destinationPath: path.resolve(renderPath, `${Name}ViewRender.php`),
                templateFile: path.resolve(__dirname, "php", "View.php.mustache"),
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
    }
  });
};
