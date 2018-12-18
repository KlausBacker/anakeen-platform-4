const gulp = require("gulp");
const { getModuleInfo } = require("../utils/moduleInfo");
const xml2js = require("xml2js");
const fs = require("fs");
const fsUtils = require("./plugins/files");
const path = require("path");
const appConst = require("../utils/appConst");

const createTemplates = require("./createWorkflow/index.js");

const getProcessXml = command => ({
  $: {
    command
  }
});

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
  name,
  parentName,
  associatedSmartStructure,
  inSelfDirectory = true,
  withParameters = true,
  withBehavior = true,
  insertIntoInfo = true
}) => {
  const wflInstallCmd = [];
  // const wflUpgradeCmd = [];
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
      let basePath = path.join("vendor", vendorName, moduleName, "Workflows");
      if (associatedSmartStructure) {
        const StructureName =
          associatedSmartStructure.charAt(0).toUpperCase() +
          associatedSmartStructure.slice(1).toLowerCase();
        basePath = path.join(
          "vendor",
          vendorName,
          moduleName,
          "SmartStructures",
          StructureName,
          `${StructureName}Workflows`
        );
      }
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
        let errorMessage = `Unable to find a setting path for the vendor (${vendorName}), you should create it or indicate the settingPath option`;
        if (associatedSmartStructure) {
          errorMessage = `Unable to find a setting path for the vendor (${vendorName}) and the structure ${associatedSmartStructure}, you should create it or indicate the settingPath option`;
        }
        throw new Error(errorMessage);
      }
      workflowPath = path.join(srcPath, basePath);
    }
    //Create the directory if needed
    let directoryPromise = Promise.resolve(workflowPath);
    const Name = name.charAt(0).toUpperCase() + name.slice(1).toLowerCase();
    if (inSelfDirectory) {
      const workflowDirectory = path.join(workflowPath, `${Name}Workflow`);
      directoryPromise = new Promise((resolve, reject) => {
        fsUtils.mkpdir(workflowDirectory, err => {
          if (err) {
            reject(err);
          }
          resolve(workflowDirectory);
        });
      });
    }
    return directoryPromise
      .then(currentPath => {
        if (!associatedSmartStructure) {
          wflInstallCmd.push(
            `./ank.php --script=importConfiguration --glob=${path.relative(
              srcPath,
              path.join(currentPath, "**", "*.xml")
            )}`
          );
        }
        // Create xml structure workflow model
        return createTemplates.Structure.writeTemplate(currentPath, {
          name,
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
            name,
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
            name,
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
      .then(() => {
        if (insertIntoInfo && !associatedSmartStructure) {
          const infoXMLPath = path.join(sourcePath, appConst.infoPath);
          const parser = new xml2js.Parser();
          return new Promise((resolve, reject) => {
            fs.readFile(infoXMLPath, { encoding: "utf8" }, (err, content) => {
              if (err) {
                return reject(err);
              }
              parser.parseString(content, (err, data) => {
                if (err) {
                  return reject(err);
                }
                //Add the xml to the postInstall and the postUpgrade tags
                const postInstall = data.module["post-install"];
                const postUpgrade = data.module["post-upgrade"];

                if (!postInstall[0].process) {
                  postInstall[0]["process"] = [];
                  if (!postInstall[0].process) {
                    // case of no empty tag
                    postInstall[0] = { process: [] };
                  }
                }
                if (!postUpgrade[0].process) {
                  postUpgrade[0].process = [];
                  if (!postUpgrade[0].process) {
                    // case of no empty tag
                    postUpgrade[0] = { process: [] };
                  }
                }

                postInstall[0].process.push(
                  ...wflInstallCmd.map(c => getProcessXml(c))
                );

                const builder = new xml2js.Builder();
                fs.writeFile(infoXMLPath, builder.buildObject(data), err => {
                  if (err) {
                    reject(err);
                  }
                  resolve();
                });
              });
            });
          });
        }
      });
  });
};

exports.createWorkflowInstance = ({
  sourcePath,
  workflowPath,
  vendorName,
  moduleName,
  modelName,
  instanceName,
  associatedSmartStructure,
  inSelfDirectory = true
}) => {
  // const wflInstallCmd = [];
  // const wflUpgradeCmd = [];
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
      throw new Error(
        "You need to specify an associated smart structure to create a workflow instance"
      );
    }
    if (!workflowPath && vendorName && moduleName) {
      //Compute and test the workflowPath for the vendor name
      const StructureName =
        associatedSmartStructure.charAt(0).toUpperCase() +
        associatedSmartStructure.slice(1).toLowerCase();
      let basePath = path.join(
        "vendor",
        vendorName,
        moduleName,
        "SmartStructures",
        StructureName,
        `${StructureName}Workflows`
      );

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
        let errorMessage = `Unable to find a setting path for the vendor (${vendorName}) and the structure ${associatedSmartStructure}, you should create it or indicate the settingPath option`;
        throw new Error(errorMessage);
      }
      workflowPath = path.join(srcPath, basePath);
    }
    //Create the directory if needed
    let directoryPromise = Promise.resolve(workflowPath);
    const InstanceName =
      instanceName.charAt(0).toUpperCase() +
      instanceName.slice(1).toLowerCase();
    if (inSelfDirectory) {
      const workflowDirectory = path.join(
        workflowPath,
        `${InstanceName}Workflow`
      );
      directoryPromise = new Promise((resolve, reject) => {
        fsUtils.mkpdir(workflowDirectory, err => {
          if (err) {
            reject(err);
          }
          resolve(workflowDirectory);
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
