const fs = require("fs");
const path = require("path");
const mustache = require("mustache");

const createAutocompletePHP = options => {
  const template = fs.readFileSync(
    path.join(__dirname, "php", "autocompletion.php.mustache"),
    "utf8"
  );
  return mustache.render(template, options);
};

exports.writeTemplate = ({ sourcePath, vendorName, moduleName, namespace }) => {
  return new Promise((resolve, reject) => {
    const autocompleteDir = path.join(
      sourcePath,
      "src",
      "vendor",
      vendorName,
      moduleName,
      "Autocompletion"
    );
    fs.mkdir(autocompleteDir, err => {
      if (err) {
        reject(err);
      } else {
        const autocompletePHP = createAutocompletePHP({
          vendorName,
          moduleName,
          namespace
        });
        fs.writeFile(
          path.join(autocompleteDir, `${moduleName}Autocompletion.php`),
          autocompletePHP,
          err => {
            if (err) {
              reject(err);
            } else {
              resolve();
            }
          }
        );
      }
    });
  });
};
