const fs = require("fs");
const fsUtils = require("../plugins/files");
const path = require("path");
const xml2js = require("xml2js");

const camelCase = require("camelcase");

const createRouteXML = ({ moduleName, vendorName, namespace }) => {
  const vendorNamePascalCase = camelCase(vendorName, { pascalCase: true });
  const moduleNamePascalCase = camelCase(moduleName, { pascalCase: true });
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
          "sde:callable": `${vendorNamePascalCase}\\${moduleNamePascalCase}\\Routes\\Main`,
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

exports.writeTemplate = (packagePath, { sourcePath, vendorName, moduleName, namespace }) => {
  const vendorNamePascalCase = camelCase(vendorName, { pascalCase: true });
  const moduleNamePascalCase = camelCase(moduleName, { pascalCase: true });
  const configPath = path.join(packagePath, "src", "vendor", vendorNamePascalCase, moduleNamePascalCase, "Config");
  return new Promise((resolve, reject) => {
    fs.mkdir(configPath, err => {
      if (err) {
        return reject(err);
      }
      const builder = new xml2js.Builder();
      const routesXml = builder.buildObject(createRouteXML({ sourcePath, vendorName, moduleName, namespace }));
      const parametersXML = builder.buildObject(createConfigParametersXML(namespace));
      fsUtils
        .writeFiles(
          {
            path: path.join(configPath, `100-${moduleNamePascalCase}Parameters.xml`),
            content: parametersXML
          },
          {
            path: path.join(configPath, `110-${moduleNamePascalCase}Routes.xml`),
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
