const fs = require("fs");
const fsUtils = require("../plugins/files");
const path = require("path");
const xml2js = require("xml2js");
const camelCase = require("camelcase");

const createGroupsXML = () => {
  return {
    "accounts:accounts": {
      $: {
        "xmlns:accounts": "https://platform.anakeen.com/4/schemas/account/1.0"
      },

      "accounts:groups": {}
    }
  };
};

const createRolesXML = () => {
  return {
    "accounts:accounts": {
      $: {
        "xmlns:accounts": "https://platform.anakeen.com/4/schemas/account/1.0"
      },

      "accounts:roles": {}
    }
  };
};

const createUsersXML = () => {
  return {
    "accounts:accounts": {
      $: {
        "xmlns:accounts": "https://platform.anakeen.com/4/schemas/account/1.0"
      },

      "accounts:users": {}
    }
  };
};

exports.writeTemplate = (packagePath, { vendorName, moduleName, namespace }) => {
  return new Promise((resolve, reject) => {
    const vendorNamePascalCase = camelCase(vendorName, { pascalCase: true });
    const moduleNamePascalCase = camelCase(moduleName, { pascalCase: true });
    const accountsDir = path.join(packagePath, "src", "vendor", vendorNamePascalCase, moduleNamePascalCase, "Accounts");
    fs.mkdir(accountsDir, err => {
      if (err) {
        return reject(err);
      }
      const builder = new xml2js.Builder();
      const groupXml = builder.buildObject(createGroupsXML(namespace));
      const rolesXml = builder.buildObject(createRolesXML(namespace));
      const usersXml = builder.buildObject(createUsersXML(namespace));
      fsUtils
        .writeFiles(
          {
            path: path.join(accountsDir, `100-${moduleNamePascalCase}Roles.xml`),
            content: rolesXml
          },
          {
            path: path.join(accountsDir, `110-${moduleNamePascalCase}Groups.xml`),
            content: groupXml
          },
          {
            path: path.join(accountsDir, `120-${moduleNamePascalCase}Users.xml`),
            content: usersXml
          }
        )
        .then(() => {
          resolve(path.join(path.relative(path.join(packagePath, "src"), accountsDir), "**", "*.xml"));
        })
        .catch(reject);
    });
  });
};
