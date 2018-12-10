const gulp = require("gulp");
const { getModuleInfo } = require("../utils/moduleInfo");
const signale = require("signale");
const fs = require("fs");
const path = require("path");
const inquirer = require("inquirer");
const { createSetting } = require("../tasks/createSetting.js");
const { checkVendorName, checkModuleName } = require("../utils/checkName");

let moduleData = {};
let settingOptions = {};

const capitalize = (str, force = false) => {
  let result = "";
  if (str && typeof str === "string") {
    result = str.charAt(0).toUpperCase();
    if (str.length) {
      if (force) {
        result += str.slice(1).toLowerCase();
      } else {
        result += str.slice(1);
      }
    }
  }
  return result;
};

const defaultPath = () => {
  let srcPath = "";
  let basePath = path.join(
    "vendor",
    settingOptions.vendorName,
    settingOptions.moduleName,
    "Settings"
  );
  if (settingOptions.associatedSS) {
    const StructureName =
      settingOptions.associatedSS.charAt(0).toUpperCase() +
      settingOptions.associatedSS.slice(1).toLowerCase();
    basePath = path.join(
      "vendor",
      settingOptions.vendorName,
      settingOptions.moduleName,
      "SmartStructures",
      StructureName,
      `${StructureName}Settings`
    );
  }
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

const SETTING_TYPES = [
  "Masks",
  "ViewControl",
  "FieldAccess",
  "Profile",
  "MailTemplate",
  "Timer",
  "Exec",
  "HelpPage",
  "Enumerate"
];
signale.config({
  displayTimestamp: true,
  displayDate: true
});

exports.desc = "Create a setting";

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
  type: {
    description: "type of the setting",
    type: "string",
    coerce: arg => {
      if (!arg) {
        throw new Error("At least one setting type must be specified");
      }
      let error = "";
      if (SETTING_TYPES.indexOf(arg) === -1) {
        error = `Invalid type ${arg}. Setting type must be one of those : [${SETTING_TYPES.join(
          ", "
        )}]`;
      }
      if (error) {
        throw new Error(error);
      }
      settingOptions.type = arg;
      return arg;
    }
  },
  associatedSmartStructure: {
    description:
      "name of the associated smart structure (empty if setting is global)",
    type: "string",
    alias: "S",
    conflicts: "associatedWorkflow",
    coerce: arg => {
      if (arg) {
        settingOptions.associatedSS = arg;
      }
      return arg;
    }
  },
  associatedWorkflow: {
    description: "name of the associated workflow",
    alias: "W",
    type: "string",
    conflicts: "associatedSmartStructure",
    coerce: arg => {
      if (arg) {
        settingOptions.associatedWFL = arg;
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
      settingOptions.vendorName = arg;
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
      settingOptions.moduleName = arg;
      return arg;
    }
  },
  name: {
    description: "name of the setting",
    alias: "n",
    type: "string",
    default: () => {
      if (moduleData.moduleInfo && settingOptions.type) {
        const ModuleName = capitalize(moduleData.moduleInfo.name);
        const structName = capitalize(settingOptions.associatedSS);
        const wflName = capitalize(settingOptions.associatedWFL);
        const Type = capitalize(settingOptions.type);
        return `${ModuleName}${structName || wflName || ""}${Type}`;
      } else {
        return [];
      }
    }
  },
  settingPath: {
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
      "add a directory for the new setting (not compatible with settingPath)",
    default: true,
    type: "boolean"
  }
};

exports.builder = builder;

const getInquirerQuestion = (paramKey, paramValue) => {
  const question = {
    type: paramValue.type === "boolean" ? "confirm" : "input",
    name: paramKey,
    message: `${paramValue.description} : `,
    default: paramValue.default,
    validate: paramValue.coerce
      ? arg => {
          try {
            paramValue.coerce(arg);
          } catch (e) {
            return e.message;
          }
          return true;
        }
      : () => true
  };
  switch (paramKey) {
    case "sourcePath":
      question.validate = arg =>
        new Promise(resolve => {
          try {
            paramValue.coerce(arg);
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
      break;
    case "type":
      question.type = "list";
      question.choices = SETTING_TYPES.map(c => {
        return {
          name: c.replace(/([A-Z][a-z]+)([A-Z][a-z]+)?/g, "$1 $2").trim(),
          value: c,
          short: c
        };
      });
      question.filter = arg => {
        question.validate(arg);
        return arg;
      };
      break;
    case "associatedWorkflow":
      question.when = arg => {
        if (arg.associatedSmartStructure) {
          return false;
        }
        return true;
      };
      break;
    case "associatedSmartStructure":
      question.when = arg => {
        if (arg.associatedWorkflow) {
          return false;
        }
        return true;
      };
      break;
  }
  return question;
};

exports.handler = async argv => {
  if (process.argv.indexOf("createSetting") === process.argv.length - 1) {
    // Mode question
    argv = await inquirer.prompt(
      Object.keys(builder).map(currentKey => {
        const currentParam = builder[currentKey];
        return getInquirerQuestion(currentKey, currentParam);
      })
    );
  }
  try {
    signale.time("createSetting");
    createSetting(argv);
    const task = gulp.task("createSetting");
    task()
      .then(() => {
        signale.timeEnd("createSetting");
        signale.success("createSetting done");
      })
      .catch(e => {
        signale.timeEnd("createSetting");
        signale.error(e);
      });
  } catch (e) {
    signale.timeEnd("createSetting");
    signale.error(e);
  }
};
