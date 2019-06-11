const gulp = require("gulp");
const { getModuleInfo } = require("../utils/moduleInfo");
const signale = require("signale");
const fs = require("fs");
const path = require("path");
const inquirer = require("inquirer");
const { createSmartStructure } = require("../tasks/createSmartStructure");
const {
  checkVendorName,
  checkSmartStructureName,
  checkModuleName
} = require("../utils/checkName");

let moduleData = {};
const structureOptions = {};
const defaultPath = () => {
  let srcPath = "";
  let basePath = path.join(
    "vendor",
    structureOptions.vendorName,
    structureOptions.moduleName,
    "SmartStructures"
  );
  if (moduleData.buildInfo) {
    //Compute and test the settingPath for the vendor name
    srcPath = moduleData.buildInfo.buildPath.find(currentPath => {
      //Check current path
      const smartPath = path.join(currentPath, basePath);
      try {
        return fs.statSync(smartPath).isDirectory();
      } catch (e) {
        return false;
      }
    });
  }
  if (!srcPath) {
    return "";
  }
  return path.join(srcPath, basePath);
};
signale.config({
  displayTimestamp: true,
  displayDate: true
});

exports.desc = "Create a smart structure";
const builder = {
  sourcePath: {
    description: "path to the info.xml directory",
    alias: "s",
    default: ".",
    type: "string",
    coerce: arg => {
      if (!fs.statSync(arg).isDirectory()) {
        throw new Error("Unable to find the source directory " + arg);
      }
      return arg;
    }
  },
  name: {
    description: "name of the smart structure",
    alias: "n",
    type: "string",
    coerce: arg => {
      if (!checkSmartStructureName(arg)) {
        throw new Error(
          "SmartStructure name must use only uppercase letter and numbers (_ authorized) , the current value is not valid : " +
            arg
        );
      }
      return arg;
    }
  },
  vendorName: {
    description: "vendor name of the module",
    alias: "v",
    default: () => {
      if (moduleData.moduleInfo) {
        return moduleData.moduleInfo.vendor;
      } else {
        return undefined;
      }
    },
    type: "string",
    coerce: arg => {
      if (!checkVendorName(arg)) {
        throw new Error(
          "Vendor name must be only a-zA-Z0-9_ , the current value is not valid : " +
            arg
        );
      }
      structureOptions.vendorName = arg;
      return arg;
    }
  },
  moduleName: {
    description: "name of the module",
    alias: "m",
    type: "string",
    default: () => {
      if (moduleData.moduleInfo) {
        return moduleData.moduleInfo.name;
      } else {
        return undefined;
      }
    },
    coerce: arg => {
      if (!checkModuleName(arg)) {
        throw new Error(
          "Module name must be only a-zA-Z0-9_ , the current value is not valid : " +
            arg
        );
      }
      structureOptions.moduleName = arg;
      return arg;
    }
  },
  parentName: {
    description: "parent name of the smartStructure",
    alias: "p",
    type: "string",
    default: "",
    coerce: arg => {
      if (!checkSmartStructureName(arg)) {
        if (!arg) {
          return arg;
        }
        throw new Error(
          "parent name must be only a-zA-Z0-9_ , the current value is not valid : " +
            arg
        );
      }
      return arg;
    }
  },
  smartStructurePath: {
    description: "path where the smart structure will be added",
    type: "string",
    default: () => {
      if (moduleData.buildInfo) {
        return defaultPath();
      }
      return "";
    },
    coerce: arg => {
      if (!arg) {
        return arg;
      }
      if (!fs.statSync(arg).isDirectory()) {
        throw new Error("Unable to find the smart structure directory " + arg);
      }
      return arg;
    }
  },
  inSelfDirectory: {
    description:
      "add a directory for the new smart structure (not compatible with smartStructurePath)",
    default: true,
    type: "boolean"
  },
  withRender: {
    description: "add renders class",
    default: true,
    type: "boolean"
  },
  withParameters: {
    description: "add parameters",
    default: true,
    type: "boolean"
  },
  withBehavior: {
    description: "add a class behavior",
    default: true,
    type: "boolean"
  },
  withSettings: {
    description: "add settings",
    default: true,
    type: "boolean"
  },
  withAutocompletion: {
    description: "add an autocompletion",
    type: "boolean",
    default: true
  },
  insertIntoInfo: {
    description: "Insert into info.xml",
    default: true,
    type: "boolean"
  }
};
exports.builder = builder;

exports.handler = async argv => {
  if (
    process.argv.indexOf("createSmartStructure") ===
    process.argv.length - 1
  ) {
    // Mode question
    argv = await inquirer.prompt(
      Object.keys(builder).map(currentKey => {
        const currentParam = builder[currentKey];
        let validateFunction = () => true;
        if (currentKey === "sourcePath") {
          validateFunction = arg =>
            new Promise(resolve => {
              try {
                currentParam.coerce(arg);
                // Fetch module infos
                getModuleInfo(arg)
                  .then(result => {
                    moduleData = result;
                    resolve(true);
                  })
                  .catch(() => {
                    resolve(false);
                  });
              } catch (e) {
                resolve(e.message);
              }
            });
        } else if (currentParam.coerce) {
          validateFunction = arg => {
            try {
              currentParam.coerce(arg);
            } catch (e) {
              return e.message;
            }
            return true;
          };
        }
        return {
          type: currentParam.type === "boolean" ? "confirm" : "input",
          name: currentKey,
          message: `${currentParam.description} : `,
          default: currentParam.default,
          validate: validateFunction
        };
      })
    );
  }
  try {
    signale.time("createSmartStructure");
    createSmartStructure(argv);
    const task = gulp.task("createSmartStructure");
    task()
      .then(() => {
        signale.timeEnd("createSmartStructure");
        signale.success("createSmartStructure done");
      })
      .catch(e => {
        signale.timeEnd("createSmartStructure");
        signale.error(e);
      });
  } catch (e) {
    signale.timeEnd("createSmartStructure");
    signale.error(e);
  }
};
