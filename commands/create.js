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

const moduleOptions = {};
signale.config({
  displayTimestamp: true,
  displayDate: true
});

const builder = {
  sourcePath: {
    description: "path to the package",
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
    type: "string",
    coerce: arg => {
      if (!checkVendorName(arg)) {
        throw new Error(
          "Vendor name must be only a-zA-Z0-9_ , the current value is not valid : " +
            arg
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
        throw new Error(
          "Namespace name must be only a-zA-Z0-9_ , the current value is not valid : " +
            arg
        );
      }
      return arg;
    }
  },
  moduleName: {
    description: "name of the module",
    alias: "m",
    type: "string",
    coerce: arg => {
      if (!checkModuleName(arg)) {
        throw new Error(
          "Module name must be only a-zA-Z0-9_ , the current value is not valid : " +
            arg
        );
      }
      moduleOptions.moduleName = arg;
      return arg;
    }
  },
  createPackage: {
    description: "create the package directory",
    alias: "c",
    default: false,
    type: "boolean"
  },
  packageName: {
    description: "package name",
    default: () => {
      if (moduleOptions && moduleOptions.moduleName && moduleOptions.vendorName) {
        return `${moduleOptions.vendorName.toLowerCase()}-${moduleOptions.moduleName.toLowerCase()}`;
      }
      return "";
    },
    implies: "createPackage"
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

const getInquirerQuestion = (currentKey, currentParam, argv) => {
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
  switch (currentKey) {
    case "packageName":
      // Ask the question only if create package option is true
      question.when = answers => !!answers.createPackage;
      break;
  }
  return question;
};

exports.handler = async argv => {
  if (!argv.moduleName || !argv.vendorName) {
    // Mode question
    argv = await inquirer.prompt(
      Object.keys(builder).map(currentKey => {
        const currentParam = builder[currentKey];
        return getInquirerQuestion(currentKey, currentParam, argv);
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
