const fs = require("fs");
const path = require("path");
const xml2js = require("xml2js");

const createEnumeratesXML = namespace => {
  return {
    "smart:config": {
      $: {
        "xmlns:smart": "https://platform.anakeen.com/4/schemas/smart/1.0"
      },
      "smart:enumerates": {
        $: {
          namespace: namespace
        }
      }
    }
  };
};

exports.writeTemplate = ({ sourcePath, vendorName, moduleName, namespace }) => {
  return new Promise((resolve, reject) => {
    const enumeratesDir = path.join(
      sourcePath,
      "src",
      "vendor",
      vendorName,
      moduleName,
      "Enumerates"
    );
    fs.mkdir(enumeratesDir, err => {
      if (err) {
        reject(err);
      } else {
        const builder = new xml2js.Builder();
        const enumeratesXml = builder.buildObject(
          createEnumeratesXML(namespace)
        );
        fs.writeFile(
          path.join(enumeratesDir, `100-${moduleName}Enumerates.xml`),
          enumeratesXml,
          err => {
            if (err) {
              reject(err);
            } else {
              resolve(
                path.relative(
                  path.join(sourcePath, "src"),
                  path.join(enumeratesDir, `100-${moduleName}Enumerates.xml`)
                )
              );
            }
          }
        );
      }
    });
  });
};
