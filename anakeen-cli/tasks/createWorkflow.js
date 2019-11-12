const gulp = require("gulp");
const { getModuleInfo } = require("../utils/moduleInfo");
const fs = require("fs");
const fsUtils = require("./plugins/files");
const path = require("path");
const camelCase = require("camelcase");

const createTemplates = require("./createWorkflow/index.js");

const convertPathInPhpNamespace = ({ vendorPath, smartStructurePath }) => {
  return path
    .relative(vendorPath, smartStructurePath)
    .split(path.sep)
    .join("\\");
};

exports.createWorkflowModel = ({
  sourcePath,
  workflowPath,
  vendorName,
  moduleName,
  smartStructureName,
  parentName,
  associatedSmartStructure,
  inSelfDirectory = true,
  withParameters = true,
  withBehavior = true
}) => {
  const modelName = smartStructureName;
  let srcPath = path.join(sourcePath, "src");
  let vendorPath = path.join(srcPath, "vendor");
  return gulp.task("createWorkflowModel", async () => {
    //Get module info
    const moduleInfo = await getModuleInfo(sourcePath);
    //Check the path for the xml file
    if (!vendorName) {
      vendorName = moduleInfo.moduleInfo.vendor;
    }
    if (!moduleName) {
      moduleName = moduleInfo.moduleInfo.name;
    }
    if (!workflowPath && !vendorName) {
      throw new Error("You need to specify a vendor name or workflowPath");
    }
    if (!workflowPath && vendorName && moduleName) {
      //Compute and test the workflowPath for the vendor name
      let basePath = path.join("vendor", vendorName, moduleName);
      if (associatedSmartStructure) {
        const StructureName = camelCase(associatedSmartStructure, { pascalCase: true });

        // a suppr
        // const StructureName =
        //   associatedSmartStructure.charAt(0).toUpperCase() + associatedSmartStructure.slice(1).toLowerCase();
        basePath = path.join("vendor", vendorName, moduleName, "SmartStructures", StructureName);
      }
      //Compute and test the workflowPath for the vendor name
      srcPath = moduleInfo.buildInfo.buildPath.find(currentPath => {
        //Check current path
        const smartPath = path.join(currentPath, basePath);
        try {
          return fs.existsSync(smartPath) && fs.statSync(smartPath).isDirectory();
        } catch (e) {
          return false;
        }
      });

      if (!srcPath) {
        let errorMessage = `Unable to find a source path for the vendor (${vendorName}), you should create it or indicate the workflowPath option`;
        if (associatedSmartStructure) {
          errorMessage = `Unable to find a the ${associatedSmartStructure} source path, you should create it or indicate the workflowPath option`;
        }
        throw new Error(errorMessage);
      }
      if (associatedSmartStructure) {
        const StructureName = camelCase(associatedSmartStructure, { pascalCase: true });

        // a suppr
        // const StructureName =
        //   associatedSmartStructure.charAt(0).toUpperCase() + associatedSmartStructure.slice(1).toLowerCase();
        basePath = path.join(basePath, `${StructureName}Workflows`);
      } else {
        basePath = path.join(basePath, "Workflows");
      }
      workflowPath = path.join(srcPath, basePath);
    }
    //Create the directory if needed
    let workflowDirectory = workflowPath;
    if (inSelfDirectory) {
      const Name = camelCase(modelName, { pascalCase: true });

      // const Name = modelName.charAt(0).toUpperCase() + modelName.slice(1).toLowerCase();
      workflowDirectory = path.join(workflowPath, `${Name}Workflow`);
    }
    let directoryPromise = Promise.resolve(workflowDirectory);
    if (!fs.existsSync(workflowDirectory) || !fs.statSync(workflowDirectory).isDirectory()) {
      directoryPromise = new Promise((resolve, reject) => {
        fsUtils.mkpdir(workflowDirectory, err => {
          if (err) {
            reject({
              fileAlreadyExist: true
            });
          }
          resolve(workflowDirectory);
        });
      });
    }
    return directoryPromise
      .then(currentPath => {
        // Create xml structure workflow model
        return createTemplates.Structure.writeTemplate(currentPath, {
          name: modelName,
          parentName,
          withBehavior,
          namespace: convertPathInPhpNamespace({
            vendorPath,
            smartStructurePath: currentPath
          })
        }).then(() => {
          return currentPath;
        });
      })
      .then(currentPath => {
        if (withParameters) {
          return createTemplates.Parameters.writeTemplate(currentPath, {
            name: modelName,
            parentName,
            withBehavior,
            namespace: convertPathInPhpNamespace({
              vendorPath,
              smartStructurePath: currentPath
            })
          }).then(() => {
            return currentPath;
          });
        }
        return currentPath;
      })
      .then(currentPath => {
        if (withBehavior) {
          return createTemplates.Behavior.writeTemplate(currentPath, {
            name: modelName,
            parentName,
            withBehavior,
            namespace: convertPathInPhpNamespace({
              vendorPath,
              smartStructurePath: currentPath
            })
          }).then(() => {
            return currentPath;
          });
        }
        return currentPath;
      });
  });
};

exports.createWorkflowInstance = ({
  sourcePath,
  workflowPath,
  vendorName,
  moduleName,
  smartStructureName,
  smartElementName,
  associatedSmartStructure,
  inSelfDirectory = true
}) => {
  const modelName = smartStructureName;
  const instanceName = smartElementName;
  let srcPath = path.join(sourcePath, "src");
  // let vendorPath = path.join(srcPath, "vendor");
  return gulp.task("createWorkflowInstance", async () => {
    //Get module info
    const moduleInfo = await getModuleInfo(sourcePath);
    //Check the path for the xml file
    if (!vendorName) {
      vendorName = moduleInfo.moduleInfo.vendor;
    }
    if (!moduleName) {
      moduleName = moduleInfo.moduleInfo.name;
    }
    if (!workflowPath && !vendorName) {
      throw new Error("You need to specify a vendor name or workflowPath");
    }

    if (!associatedSmartStructure) {
      throw new Error("You need to specify an associated smart structure to create a workflow instance");
    }
    if (!workflowPath && vendorName && moduleName) {
      //Compute and test the workflowPath for the vendor name
      const StructureName = camelCase(associatedSmartStructure, { pascalCase: true });

      // const StructureName =
      //   associatedSmartStructure.charAt(0).toUpperCase() + associatedSmartStructure.slice(1).toLowerCase();
      let basePath = path.join("vendor", vendorName, moduleName, "SmartStructures", StructureName);

      //Compute and test the workflowPath for the vendor name
      srcPath = moduleInfo.buildInfo.buildPath.find(currentPath => {
        //Check current path
        const smartPath = path.join(currentPath, basePath);
        try {
          return fs.statSync(smartPath).isDirectory();
        } catch (e) {
          return false;
        }
      });
      if (!srcPath) {
        srcPath = path.join(sourcePath, "src", "vendor", vendorName, moduleName, "Workflow");
      }
      workflowPath = path.join(srcPath, basePath, `${StructureName}Workflows`);
    }
    if (inSelfDirectory) {
      const InstanceName = camelCase(instanceName, { pascalCase: true });

      // const InstanceName = instanceName.charAt(0).toUpperCase() + instanceName.slice(1).toLowerCase();
      workflowPath = path.join(workflowPath, `${InstanceName}Workflow`);
    }
    //Create the directory if needed
    let directoryPromise = Promise.resolve(workflowPath);

    if (!fs.existsSync(workflowPath) || !fs.statSync(workflowPath).isDirectory()) {
      directoryPromise = new Promise((resolve, reject) => {
        fsUtils.mkpdir(workflowPath, err => {
          if (err) {
            reject(err);
          }
          resolve(workflowPath);
        });
      });
    }
    return directoryPromise
      .then(currentPath => {
        // Create xml structure workflow model
        return createTemplates.Settings.writeTemplate(currentPath, {
          instanceName,
          modelName,
          associatedSmartStructure
        }).then(() => {
          return currentPath;
        });
      })
      .then(currentPath => {
        return createTemplates.Permissions.writeTemplate(currentPath, {
          instanceName,
          modelName,
          associatedSmartStructure
        }).then(() => {
          return currentPath;
        });
      })
      .then(currentPath => {
        return createTemplates.Access.writeTemplate(currentPath, {
          instanceName,
          modelName,
          associatedSmartStructure
        }).then(() => {
          return currentPath;
        });
      });
  });
};
