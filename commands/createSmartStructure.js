const gulp = require("gulp");
const signale = require("signale");
const fs = require("fs");
const inquirer = require("inquirer");
const { createSmartStructure } = require("../tasks/createSmartStructure");
const { checkVendorName, checkSmartStructureName } = require("../utils/checkName");

signale.config({
  displayTimestamp: true,
  displayDate: true
});

exports.desc = "Create a smart structure";
const builder = {
  sourcePath: {
    defaultDescription: "path to the module",
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
    defaultDescription: "name of the smart structure",
    alias: "n",
    type: "string",
    coerce: arg => {
      if (!checkSmartStructureName(arg)) {
        throw new Error(
          "SmartStructure name must be only a-zA-Z0-9_ , the current value is not valid : " +
            arg
        );
      }
      return arg;
    }
  },
  vendorName: {
    defaultDescription: "vendor name of the module",
    alias: "v",
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
    defaultDescription: "parent name of the smartStructure",
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
    defaultDescription: "path where the smart structure will be added",
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
    defaultDescription:
      "add a directory for the new smart structure (not compatible with smartStructurePath)",
    default: true,
    type: "boolean"
  },
  withRender: {
    defaultDescription: "add renders class",
    default: true,
    type: "boolean"
  },
  withClass: {
    defaultDescription: "add a class",
    default: true,
    type: "boolean"
  },
  insertIntoInfo: {
    defaultDescription: "Insert into info.xml",
    default: true,
    type: "boolean"
  }
};
exports.builder = builder;

exports.handler = async argv => {
  if (!argv.name) {
    // Mode question
    argv = await inquirer.prompt(
      Object.keys(builder).map(currentKey => {
        const currentParam = builder[currentKey];
        return {
          type: currentParam.type === "boolean" ? "confirm" : "input",
          name: currentKey,
          message: `${currentParam.defaultDescription} : `,
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
