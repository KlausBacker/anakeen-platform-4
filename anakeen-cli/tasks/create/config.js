const fs = require("fs");
const fsUtils = require("../plugins/files");
const path = require("path");
const xml2js = require("xml2js");

const createRouteXML = ({ moduleName, vendorName, namespace }) => {
  return {
    "sde:config": {
      $: {
        "xmlns:sde": "https://platform.anakeen.com/4/schemas/sde/1.0"
      },
      "sde:routes": {
        $: {
          namespace: namespace
        },
        "sde:route": {
          $: { name: `${moduleName}Main` },
          "sde:callable": `${vendorName}\\${moduleName}\\Routes\\Main`,
          "sde:method": "GET",
          "sde:pattern": `/${moduleName}/main`,
          "sde:description": `A route example for ${moduleName}`
        }
      },
      "sde:accesses": {
        $: {
          namespace: namespace
        }
      }
    }
  };
};

const createConfigParametersXML = namespace => {
  return {
    "sde:config": {
      $: {
        "xmlns:sde": "https://platform.anakeen.com/4/schemas/sde/1.0"
      },
      "sde:parameters": {
        $: {
          namespace: namespace
        }
      }
    }
  };
};

exports.writeTemplate = (
  packagePath,
  { sourcePath, vendorName, moduleName, namespace }
) => {
  const configPath = path.join(
    packagePath,
    "src",
    "vendor",
    vendorName,
    moduleName,
    "Config"
  );
  return new Promise((resolve, reject) => {
    fs.mkdir(configPath, err => {
      if (err) {
        return reject(err);
      }
      const builder = new xml2js.Builder();
      const routesXml = builder.buildObject(
        createRouteXML({ sourcePath, vendorName, moduleName, namespace })
      );
      const parametersXML = builder.buildObject(
        createConfigParametersXML(namespace)
      );
      fsUtils
        .writeFiles(
          {
            path: path.join(configPath, `100-${moduleName}Parameters.xml`),
            content: parametersXML
          },
          {
            path: path.join(configPath, `110-${moduleName}Routes.xml`),
            content: routesXml
          }
        )
        .then(() => {
          resolve(path.relative(path.join(packagePath, "src"), configPath));
        })
        .catch(reject);
    });
  });
};