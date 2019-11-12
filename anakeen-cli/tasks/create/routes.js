const fs = require("fs");
const path = require("path");
const mustache = require("mustache");
const camelCase = require("camelcase");

const createRoutePHP = options => {
  const template = fs.readFileSync(path.join(__dirname, "php", "routes.php.mustache"), "utf8");
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
      "Routes"
    );
    fs.mkdir(autocompleteDir, err => {
      if (err) {
        reject(err);
      } else {
        const autocompletePHP = createRoutePHP({
          vendorName,
          moduleName,
          namespace
        });
        fs.writeFile(path.join(autocompleteDir, `Main.php`), autocompletePHP, err => {
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
