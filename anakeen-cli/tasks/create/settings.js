const fs = require("fs");
const path = require("path");
const camelCase = require("camelcase");

exports.writeTemplate = (packagePath, { vendorName, moduleName }) => {
  return new Promise((resolve, reject) => {
    const vendorNamePascalCase = camelCase(vendorName, { pascalCase: true });
    const moduleNamePascalCase = camelCase(moduleName, { pascalCase: true });
    const settingsDir = path.join(packagePath, "src", "vendor", vendorNamePascalCase, moduleNamePascalCase, "Settings");
    fs.mkdir(settingsDir, err => {
      if (err) {
        reject(err);
      } else {
        fs.mkdir(path.join(settingsDir, "100-ViewControls"), vcErr => {
          if (vcErr) {
            reject(vcErr);
          } else {
            fs.mkdir(path.join(settingsDir, "200-FieldAccesses"), faErr => {
              if (faErr) {
                reject(faErr);
              } else {
                resolve();
              }
            });
          }
        });
      }
    });
  });
};
