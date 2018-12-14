const gulp = require("gulp");
const { getModuleInfo } = require("../utils/moduleInfo");
const signale = require("signale");
const fs = require("fs");
const path = require("path");
const inquirer = require("inquirer");
const {
  createWorkflowInstance,
  createWorkflowModel
} = require("../tasks/createWorkflow");
const {
  checkVendorName,
  checkSmartStructureName,
  checkModuleName
} = require("../utils/checkName");

let moduleData = {};
const wflOptions = {};
const defaultPath = () => {
  let srcPath = "";
  let basePath = path.join(
    "vendor",
    wflOptions.vendorName,
    wflOptions.moduleName,
    "Workflows"
  );
  if (wflOptions.associatedSS) {
    const Name =
      wflOptions.associatedSS.charAt(0).toUpperCase() +
      wflOptions.associatedSS.slice(1).toLowerCase();
    basePath = path.join(
      "vendor",
      wflOptions.vendorName,
      wflOptions.moduleName,
      "SmartStructures",
      Name
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
signale.config({
  displayTimestamp: true,
  displayDate: true
});

exports.desc = "Create a workflow";
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
      wflOptions.vendorName = arg;
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
      wflOptions.moduleName = arg;
      return arg;
    }
  },
  modelName: {
    description: "workflow model name (empty to create a new model)",
    alias: "m",
    default: "",
    type: "string",
    coerce: arg => {
      if (!arg) {
        return arg;
      }
      if (!checkSmartStructureName(arg)) {
        throw new Error(
          "Workflow model name must use only uppercase letter and numbers (_ authorized) , the current value is not valid : " +
            arg
        );
      }
      return arg;
    }
  },
  name: {
    description: "name of the workflow model",
    alias: "n",
    type: "string",
    coerce: arg => {
      if (!checkSmartStructureName(arg)) {
        throw new Error(
          "Workflow name must use only uppercase letter and numbers (_ authorized) , the current value is not valid : " +
            arg
        );
      }
      wflOptions.name = arg;
      return arg;
    }
  },
  parentName: {
    description: "parent name of the workflow model",
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
  instanceName: {
    description: "name of the workflow instance",
    type: "string",
    coerce: arg => {
      if (!checkSmartStructureName(arg)) {
        throw new Error(
          "Workflow instance name must use only uppercase letter and numbers (_ authorized) , the current value is not valid : " +
            arg
        );
      }
      wflOptions.name = arg;
      return arg;
    }
  },
  associatedSmartStructure: {
    description: "name of the associated smart structure",
    type: "string",
    alias: "S",
    coerce: arg => {
      if (arg) {
        wflOptions.associatedSS = arg;
      }
      return arg;
    }
  },
  workflowPath: {
    description: "path where the workflow will be added",
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
      "add a directory for the new workflow (not compatible with workflowPath)",
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
  insertIntoInfo: {
    description: "Insert into info.xml",
    default: true,
    type: "boolean"
  }
};
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
  switch (currentKey) {
    case "sourcePath":
      question.validate = arg =>
        new Promise(resolve => {
          try {
            currentParam.coerce(arg);
            // Fetch module infos
            getModuleInfo(arg)
              .then(result => {
                moduleData = result;
                resolve(true);
              })
              .catch(err => {
                resolve(err.message);
              });
          } catch (e) {
            resolve(e.message);
          }
        });
      break;
    case "name":
    case "parentName":
      question.when = argv => !argv.modelName;
      break;
  }
  return question;
};

exports.handler = async argv => {
  if (process.argv.indexOf("createWorkflow") === process.argv.length - 1) {
    // Mode question
    argv = await inquirer.prompt(
      Object.keys(builder).map(currentKey => {
        const currentParam = builder[currentKey];
        return getInquirerQuestion(currentKey, currentParam);
      })
    );
  }
  try {
    signale.time("createWorkflow");
    let task;
    let subtask = Promise.resolve();
    if (argv.modelName) {
      createWorkflowInstance(argv);
      task = gulp.task("createWorkflowInstance");
    } else {
      createWorkflowModel(argv);
      task = gulp.task("createWorkflowModel");
      createWorkflowInstance(Object.assign({}, argv, { modelName: argv.name }));
      subtask = gulp.task("createWorkflowInstance");
    }
    task()
      .then(() => {
        return subtask;
      })
      .then(() => {
        signale.timeEnd("createWorkflow");
        signale.success("createWorkflow done");
      })
      .catch(e => {
        signale.timeEnd("createWorkflow");
        signale.error(e);
      });
  } catch (e) {
    signale.timeEnd("createWorkflow");
    signale.error(e);
  }
};
