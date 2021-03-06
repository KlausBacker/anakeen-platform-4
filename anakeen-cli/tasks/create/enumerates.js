const fs = require("fs");
const path = require("path");
const xml2js = require("xml2js");
const camelCase = require("camelcase");

const createEnumeratesXML = () => {
  return {
    "smart:config": {
      $: {
        "xmlns:smart": "https://platform.anakeen.com/4/schemas/smart/1.0"
      },
      "smart:enumerates": {}
    }
  };
};

exports.writeTemplate = (packagePath, { vendorName, moduleName, namespace }) => {
  return new Promise((resolve, reject) => {
    const vendorNamePascalCase = camelCase(vendorName, { pascalCase: true });
    const moduleNamePascalCase = camelCase(moduleName, { pascalCase: true });
    const enumeratesDir = path.join(
      packagePath,
      "src",
      "vendor",
      vendorNamePascalCase,
      moduleNamePascalCase,
      "Enumerates"
    );
    fs.mkdir(enumeratesDir, err => {
      if (err) {
        reject(err);
      } else {
        const builder = new xml2js.Builder();
        const enumeratesXml = builder.buildObject(createEnumeratesXML(namespace));
        fs.writeFile(path.join(enumeratesDir, `100-${moduleNamePascalCase}Enumerates.xml`), enumeratesXml, err => {
          if (err) {
            reject(err);
          } else {
            resolve(path.relative(path.join(packagePath, "src"), path.join(enumeratesDir, "**", "*.xml")));
          }
        });
      }
    });
  });
};
