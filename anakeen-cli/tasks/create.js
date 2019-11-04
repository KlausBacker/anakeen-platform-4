const gulp = require("gulp");
const fs = require("fs");
const path = require("path");
const xml2js = require("xml2js");

const fsUtils = require("./plugins/files");
const createTemplates = require("./create/index.js");

const { checkModuleName, checkVendorName, checkNamespace } = require("../utils/checkName");

const createInfoXML = ({ moduleName, vendorName }, postInstall = {}, postUpgrade = {}) => {
  return {
    module: {
      $: {
        xmlns: "https://platform.anakeen.com/4/schemas/app/1.0",
        name: moduleName,
        vendor: vendorName,
        version: "1.0.0"
      },
      "post-install": postInstall,
      "post-upgrade": postUpgrade
    }
  };
};

const createBuildXML = ({ moduleName, vendorName }, isSmartStructure) => {
  let xml = "";

  if (isSmartStructure) {
    xml = {
      "acli:config": {
        $: {
          "xmlns:acli": "https://platform.anakeen.com/4/schemas/module/1.0"
        },
        "acli:source": {
          $: { path: "src" }
        },
        "acli:stub-config": {
          "acli:stub-struct": {
            $: { source: path.join("src/vendor", vendorName, moduleName, "SmartStructures") }
          }
        }
      }
    };
  } else {
    xml = {
      "acli:config": {
        $: {
          "xmlns:acli": "https://platform.anakeen.com/4/schemas/module/1.0"
        },
        "acli:source": {
          $: { path: "src" }
        }
      }
    };
  }
  return xml;
};

const createCommand = command => {
  return {
    process: {
      $: {
        command: command
      }
    }
  };
};

exports.create = options => {
  const {
    sourcePath,
    moduleName,
    vendorName,
    namespace,
    withSmartStructure,
    withConfig,
    withPublic,
    withAccount,
    withAutocompletion,
    withRoutes,
    withEnumerates,
    withSettings
  } = options;
  let postUpgrade = {
    process: []
  };
  let postInstall = { process: [] };
  return gulp.task("create", () => {
    //Create the vendor dir
    return new Promise((resolve, reject) => {
      //Check name of the element
      if (!checkModuleName(moduleName)) {
        reject("The module name is invalid " + moduleName);
      }
      if (!checkVendorName(vendorName)) {
        reject("The vendor name is invalid " + vendorName);
      }
      if (!checkNamespace(namespace)) {
        reject("The namespace is invalid " + namespace);
      }
      let completePath = path.join(sourcePath, "src", "vendor", vendorName, moduleName);

      if (withSmartStructure) {
        completePath = path.join(completePath, "SmartStructures");
      }
      fsUtils.mkpdir(completePath, err => {
        if (err) {
          return reject(err);
        }
        resolve();
      });
    }) // Create the public (if needed)
      .then(() => {
        if (withPublic) {
          return createTemplates.public.writeTemplate(sourcePath, options);
        }
        return Promise.resolve();
      })
      .then(() => {
        if (withConfig) {
          return createTemplates.config.writeTemplate(sourcePath, options).then(toImport => {
            postInstall.process.push(
              createCommand(`./ank.php --script=registerConfigDir --path=./${toImport}`).process
            );
            return Promise.resolve();
          });
        }
        return Promise.resolve();
      })
      .then(() => {
        if (withAccount) {
          return createTemplates.accounts.writeTemplate(sourcePath, options).then(toImport => {
            const command = createCommand(`./ank.php --script=importConfiguration --glob=./${toImport}`);
            postInstall.process.push(command.process);
            postUpgrade.process.push(command.process);
            return Promise.resolve();
          });
        }
        return Promise.resolve();
      })
      .then(() => {
        if (withAutocompletion) {
          return createTemplates.autocompletion.writeTemplate(sourcePath, options);
        }
        return Promise.resolve();
      })
      .then(() => {
        if (withEnumerates) {
          return createTemplates.enumerates.writeTemplate(sourcePath, options).then(toImport => {
            const command = createCommand(`./ank.php --script=importConfiguration --glob=./${toImport}`);
            postInstall.process.push(command.process);
            postUpgrade.process.push(command.process);
            return Promise.resolve();
          });
        }
        return Promise.resolve();
      })
      .then(() => {
        if (withSettings) {
          return createTemplates.settings.writeTemplate(sourcePath, options);
        }
        return Promise.resolve();
      })
      .then(() => {
        if (withRoutes) {
          return createTemplates.routes.writeTemplate(sourcePath, options);
        }
        return Promise.resolve();
      })
      .then(() => {
        return new Promise((resolve, reject) => {
          const builder = new xml2js.Builder();
          const xml = builder.buildObject(createInfoXML({ moduleName, vendorName }, postInstall, postUpgrade));
          fs.writeFile(path.join(sourcePath, "info.xml"), xml, err => {
            if (err) {
              return reject(err);
            }
            resolve();
          });
        });
      })
      .then(() => {
        return new Promise((resolve, reject) => {
          const builder = new xml2js.Builder();
          const xml = builder.buildObject(createBuildXML({ moduleName, vendorName }, withSmartStructure));
          fs.writeFile(path.join(sourcePath, "build.xml"), xml, err => {
            if (err) {
              return reject(err);
            }
            resolve();
          });
        });
      });
  });
};
