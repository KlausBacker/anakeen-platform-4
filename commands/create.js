const gulp = require("gulp");
const { create } = require("../tasks/create");
const signale = require("signale");
const fs = require("fs");
const inquirer = require("inquirer");

const {
  checkModuleName,
  checkVendorName,
  checkNamespace
} = require("../utils/checkName");

signale.config({
  displayTimestamp: true,
  displayDate: true
});

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
  moduleName: {
    defaultDescription: "name of the module",
    alias: "m",
    type: "string",
    coerce: arg => {
      if (!checkModuleName(arg)) {
        throw new Error(
          "Module name must be only a-zA-Z0-9_ , the current value is not valid : " +
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
  namespace: {
    defaultDescription:
      "namespace (if void the namespace is equal to the vendorName)",
    alias: "n",
    type: "string",
    coerce: arg => {
      if (arg && !checkNamespace(arg)) {
        throw new Error(
          "Namespace name must be only a-zA-Z0-9_ , the current value is not valid : " +
            arg
        );
      }
      return arg;
    }
  },
  withSmartStructure: {
    defaultDescription: "add path for smart structure",
    default: true,
    type: "boolean"
  },
  withConfig: {
    defaultDescription: "add path and file for route",
    default: true,
    type: "boolean"
  },
  withPublic: {
    defaultDescription: "add public path",
    alias: "p",
    default: true,
    type: "boolean"
  }
};

exports.desc = "Create a module";
exports.builder = builder;

exports.handler = async argv => {
  if (!argv.moduleName || !argv.vendorName) {
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
  if (!argv.namespace) {
    argv.namespace = argv.vendorName;
  }
  try {
    signale.time("create");
    create(argv);
    const task = gulp.task("create");
    task()
      .then(() => {
        signale.timeEnd("create");
        signale.success("create done");
      })
      .catch(e => {
        signale.timeEnd("create");
        signale.error(e);
      });
  } catch (e) {
    signale.timeEnd("create");
    signale.error(e);
  }
};
