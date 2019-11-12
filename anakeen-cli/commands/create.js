const gulp = require("gulp");
const { create } = require("../tasks/create");
const signale = require("signale");
const fs = require("fs");
const inquirer = require("inquirer");

const { checkModuleName, checkVendorName, checkNamespace } = require("../utils/checkName");

const moduleOptions = {};
signale.config({
  displayTimestamp: true,
  displayDate: true
});

const builder = {
  sourcePath: {
    description: "path to the package [Mandatory]",
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
    description: "vendor name of the module [Mandatory]",
    alias: "v",
    type: "string",
    coerce: arg => {
      if (!checkVendorName(arg)) {
        throw new Error(
          "Vendor name must be only a-zA-Z0-9 and in PascalCase, the current value is not valid : " + arg
        );
      }
      moduleOptions.vendorName = arg;
      return arg;
    }
  },
  namespace: {
    description: "namespace (if void the namespace is equal to the vendorName)",
    alias: "n",
    type: "string",
    coerce: arg => {
      if (arg && !checkNamespace(arg)) {
        throw new Error("Namespace name must be only a-zA-Z0-9_ , the current value is not valid : " + arg);
      }
      return arg;
    }
  },
  moduleName: {
    description: "name of the module [Mandatory]",
    alias: "m",
    type: "string",
    coerce: arg => {
      if (!checkModuleName(arg)) {
        throw new Error(
          "Module name must be only a-zA-Z0-9 and in PascalCase, the current value is not valid : " + arg
        );
      }
      moduleOptions.moduleName = arg;
      return arg;
    }
  },
  withSmartStructure: {
    description: "add path for smart structure",
    default: true,
    type: "boolean"
  },
  withConfig: {
    description: "add config path and xml files for routes",
    default: true,
    type: "boolean"
  },
  withPublic: {
    description: "add public path",
    alias: "p",
    default: true,
    type: "boolean"
  },
  withAccount: {
    description: "add path and files for accounts",
    default: true,
    type: "boolean"
  },
  withAutocompletion: {
    description: "add path and php file for autocompletion",
    default: true,
    type: "boolean"
  },
  withEnumerates: {
    description: "add path and files for enumerates",
    default: true,
    type: "boolean"
  },
  withSettings: {
    description: "add path and files for settings",
    default: true,
    type: "boolean"
  },
  withRoutes: {
    description: "add php file example for route",
    default: true,
    implies: "withConfig",
    type: "boolean"
  }
};

exports.desc = "Create a module";
exports.builder = builder;

const getInquirerQuestion = (currentKey, currentParam) => {
  const question = {
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
  return question;
};

exports.handler = async argv => {
  let args = argv;
  if (!argv.moduleName || !argv.vendorName) {
    // Mode question
    args = await inquirer.prompt(
      Object.keys(builder).map(currentKey => {
        const currentParam = builder[currentKey];
        return getInquirerQuestion(currentKey, currentParam, args);
      })
    );
  }
  if (!args.namespace) {
    args.namespace = args.vendorName;
  }
  try {
    signale.time("create");
    create(args);
    const task = gulp.task("create");
    task()
      .then(() => {
        signale.timeEnd("create");
        signale.success("create done");
      })
      .catch(e => {
        signale.timeEnd("create");
        signale.error(e);
        process.exit(1);
      });
  } catch (e) {
    signale.timeEnd("create");
    signale.error(e);
    process.exit(1);
  }
};
