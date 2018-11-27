const gulp = require("gulp");
const { getModuleInfo } = require("../utils/moduleInfo");
const signale = require("signale");
const fs = require("fs");
const inquirer = require("inquirer");
const { createSmartStructure } = require("../tasks/createSmartStructure");
const {
  checkVendorName,
  checkSmartStructureName
} = require("../utils/checkName");

let moduleData = {};
signale.config({
  displayTimestamp: true,
  displayDate: true
});

exports.desc = "Create a smart structure";
const builder = {
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
  sourcePath: {
    description: "path to the module",
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
    default: "",
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
  withClass: {
    description: "add a class",
    default: true,
    type: "boolean"
  },
  insertIntoInfo: {
    description: "Insert into info.xml",
    default: true,
    type: "boolean"
  }
};
exports.builder = builder;

exports.handler = async argv => {
  if (!argv.name) {
    // Mode question
    moduleData = await getModuleInfo(argv.sourcePath);
    argv = await inquirer.prompt(
      Object.keys(builder).map(currentKey => {
        const currentParam = builder[currentKey];
        return {
          type: currentParam.type === "boolean" ? "confirm" : "input",
          name: currentKey,
          message: `${currentParam.description} : `,
          default: currentParam.default,
          validate: currentParam.coerce
            ? arg => {
                try {
                  currentParam.coerce(arg);
                } catch (e) {
                  return e.message;
                }
                return true;
              }
            : () => true
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
