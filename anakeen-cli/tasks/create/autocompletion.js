const fs = require("fs");
const path = require("path");
const mustache = require("mustache");
const camelCase = require("camelcase");

const createAutocompletePHP = options => {
  const template = fs.readFileSync(path.join(__dirname, "php", "autocompletion.php.mustache"), "utf8");
  return mustache.render(template, options);
};

exports.writeTemplate = (packagePath, { vendorName, moduleName, namespace }) => {
  return new Promise((resolve, reject) => {
    const vendorNamePascalCase = camelCase(vendorName, { pascalCase: true });
    const moduleNamePascalCase = camelCase(moduleName, { pascalCase: true });
    const autocompleteDir = path.join(
      packagePath,
      "src",
      "vendor",
      vendorNamePascalCase,
      moduleNamePascalCase,
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
        fs.writeFile(path.join(autocompleteDir, `${moduleName}Autocompletion.php`), autocompletePHP, err => {
          if (err) {
            reject(err);
          } else {
            resolve();
          }
        });
      }
    });
  });
};
