const fs = require("fs");
const fsUtils = require("../plugins/files");
const path = require("path");
const xml2js = require("xml2js");

const createGroupsXML = namespace => {
  return {
    accounts: {
      groups: {
        $: {
          namespace: namespace
        }
      }
    }
  };
};

const createRolesXML = namespace => {
  return {
    accounts: {
      roles: {
        $: {
          namespace: namespace
        }
      }
    }
  };
};

const createUsersXML = namespace => {
  return {
    accounts: {
      users: {
        $: {
          namespace: namespace
        }
      }
    }
  };
};

exports.writeTemplate = (
  packagePath,
  { vendorName, moduleName, namespace }
) => {
  return new Promise((resolve, reject) => {
    const accountsDir = path.join(
      packagePath,
      "src",
      "vendor",
      vendorName,
      moduleName,
      "Accounts"
    );
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
            path: path.join(accountsDir, `100-${moduleName}Groups.xml`),
            content: groupXml
          },
          {
            path: path.join(accountsDir, `110-${moduleName}Roles.xml`),
            content: rolesXml
          },
          {
            path: path.join(accountsDir, `120-${moduleName}Users.xml`),
            content: usersXml
          }
        )
        .then(() => {
          resolve(
            path.join(
              path.relative(path.join(packagePath, "src"), accountsDir),
              "**",
              "*.xml"
            )
          );
        })
        .catch(reject);
    });
  });
};
