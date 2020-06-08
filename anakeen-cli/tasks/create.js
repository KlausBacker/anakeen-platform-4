const gulp = require("gulp");
const fs = require("fs");
const path = require("path");
const xml2js = require("xml2js");

const fsUtils = require("./plugins/files");
const createTemplates = require("./create/index.js");

const camelCase = require("camelcase");

const { checkModuleName, checkVendorName, checkNamespace } = require("../utils/checkName");

const createInfoXML = ({ moduleName, vendorName }, postInstall = {}, postUpgrade = {}) => {
  const vendorNamePascalCase = camelCase(vendorName, { pascalCase: true });
  const moduleNamePascalCase = camelCase(moduleName, { pascalCase: true });
  return {
    module: {
      $: {
        xmlns: "https://platform.anakeen.com/4/schemas/app/1.0",
        name: moduleNamePascalCase,
        vendor: vendorNamePascalCase,
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
    const vendorNamePascalCase = camelCase(vendorName, { pascalCase: true });
    const moduleNamePascalCase = camelCase(moduleName, { pascalCase: true });

    xml = {
      "acli:config": {
        $: {
          "xmlns:acli": "https://platform.anakeen.com/4/schemas/module/1.0"
        },
        "acli:source": {
          $: { path: "src" }
        },
        "acli:po-config": {
          "acli:po-struct": {
            $: {
              source: path.join("src/vendor", vendorNamePascalCase, moduleNamePascalCase, "SmartStructures/**/*xml")
            }
          },
          "acli:po-enum": {
            $: {
              source: path.join("src/vendor", vendorNamePascalCase, moduleNamePascalCase, "Enumerates/**/*xml")
            }
          },
          "acli:po-cvdoc": {
            $: {
              source: path.join(
                "src/vendor",
                vendorNamePascalCase,
                moduleNamePascalCase,
                "Settings/100-ViewControls/**/*xml"
              )
            }
          },
          "acli:po-workflow": {
            $: {
              source: path.join(
                "src/vendor",
                vendorNamePascalCase,
                moduleNamePascalCase,
                "SmartStructures/**/Workflow/**/*xml"
              )
            }
          },
          "acli:po-mustache": {
            $: {
              source: path.join("src/vendor", vendorNamePascalCase, moduleNamePascalCase, "**/*mustache"),
              target: `${moduleNamePascalCase}-Mustache`
            }
          },
          "acli:po-php": {
            $: {
              source: path.join("src/vendor", vendorNamePascalCase, moduleNamePascalCase, "**/*php"),
              target: `${moduleNamePascalCase}-php`
            }
          },
          "acli:po-js": {
            $: {
              source: path.join("src/vendor", vendorNamePascalCase, moduleNamePascalCase, "**/*js"),
              target: `${moduleNamePascalCase}-js`
            }
          },
          "acli:po-vuejs": {
            $: {
              source: path.join("src/vendor", vendorNamePascalCase, moduleNamePascalCase, "**/*.?(vue|js)"),
              target: `${moduleNamePascalCase}-VueJs`
            }
          }
        },
        "acli:stub-config": {
          "acli:stub-struct": {
            $: {
              source: path.join("src/vendor", vendorNamePascalCase, moduleNamePascalCase, "SmartStructures/**/*xml")
            }
          },
          "acli:stub-workflow": {
            $: {
              source: path.join("src/vendor", vendorNamePascalCase, moduleNamePascalCase, "Workflows/**/*Graph.xml")
            }
          },
          "acli:stub-enumerate": {
            $: {
              source: path.join("src/vendor", vendorNamePascalCase, moduleNamePascalCase, "SmartStructures/**/*xml")
            }
          },
          "acli:stub-struct-js-config": {
            $: {
              target: "constants/SmartStructuresFields.js"
            }
          },
          "acli:stub-enum-js-config": {
            $: {
              target: "constants/enumerates/"
            }
          },
          "acli:stub-wfl-js-config": {
            $: {
              target: "constants/workflows/"
            }
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
      const vendorNamePascalCase = camelCase(options.vendorName, { pascalCase: true });
      const moduleNamePascalCase = camelCase(options.moduleName, { pascalCase: true });

      let completePath = path.join(sourcePath, "src", "vendor", vendorNamePascalCase, moduleNamePascalCase);
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
