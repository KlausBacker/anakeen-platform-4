const gulp = require("gulp");
const { getModuleInfo } = require("../utils/moduleInfo");
const xml2js = require("xml2js");
const fs = require("fs");
const path = require("path");
const appConst = require("../utils/appConst");
const fsUtils = require("./plugins/files");
const signale = require("signale");
const createTemplates = require("./createSmartStructure/index.js");
const camelCase = require("camelcase");

const getProcessXml = command => ({
  $: {
    command
  }
});

String.prototype.replaceAll = function(search, replacement) {
  return this.replace(new RegExp(search, "g"), replacement);
};

// eslint-disable-next-line no-unused-vars
const convertPathInPhpNamespace = ({ vendorPath, smartStructurePath }) => {
  const tmpNameSpacePhp = smartStructurePath.split("vendor/");

  return tmpNameSpacePhp[1].replaceAll("/", "\\");
};

exports.createSmartStructure = ({
  sourcePath,
  smartStructurePath,
  vendorName,
  moduleName,
  name,
  inSelfDirectory = true,
  withRender = true,
  withBehavior = true,
  withSettings = true,
  withParameters = true,
  withAutocompletion = true,
  workflow,
  parentName = false,
  insertIntoInfo = true
}) => {
  const ssInstallCmd = [];
  const ssUpgradeCmd = [];
  let srcPath = path.join(sourcePath, "src");
  let modulePath = srcPath;
  let vendorPath = path.join(srcPath, "vendor");
  return gulp.task("createSmartStructure", async () => {
    //Get module info
    const moduleInfo = await getModuleInfo(sourcePath);
    //Check the path for the xml file
    if (!vendorName) {
      vendorName = moduleInfo.moduleInfo.vendor;
    }
    if (!moduleName) {
      moduleName = moduleInfo.moduleInfo.name;
    }
    if (!smartStructurePath && !vendorName) {
      throw new Error("You need to specify a vendor name or smartStructurePath");
    }
    if (!smartStructurePath && vendorName && moduleName) {
      //Compute and test the smartStructurePath for the vendor name
      modulePath = moduleInfo.buildInfo.buildPath.find(currentPath => {
        //Check current path
        const smartPath = path.join(currentPath, "vendor", vendorName, moduleName, "SmartStructures");
        try {
          return fs.existsSync(smartPath) && fs.statSync(smartPath).isDirectory();
        } catch (e) {
          return "";
        }
      });

      if (!modulePath) {
        modulePath = path.join(sourcePath, "src", "vendor", vendorName, moduleName, "SmartStructures");
        fsUtils.mkpdir(modulePath, err => {
          if (err) {
            // eslint-disable-next-line no-console
            console.error(err);
          }
          signale.info("No Smart structure directory given : use default path (" + modulePath + ")");
        });
      }
      if (modulePath === path.join(sourcePath, "src")) {
        modulePath = path.join(sourcePath, "src", "vendor", vendorName, moduleName, "SmartStructures");
      }
      smartStructurePath = modulePath;
      vendorPath = path.join(srcPath, "vendor");
    }
    //Create the directory if needed
    let directoryPromise = Promise.resolve(smartStructurePath);
    const Name = camelCase(name, { pascalCase: true });

    if (inSelfDirectory) {
      const smartStructureDirectory = path.join(smartStructurePath, Name);
      directoryPromise = new Promise((resolve, reject) => {
        fsUtils.mkpdir(smartStructureDirectory, err => {
          if (err) {
            reject(err);
          }
          resolve(smartStructureDirectory);
        });
      });
    }
    return directoryPromise
      .then(currentPath => {
        // Create xml structure
        return createTemplates.Structure.writeTemplate(currentPath, {
          name,
          parentName,
          withBehavior,
          namespace: convertPathInPhpNamespace({
            vendorPath,
            smartStructurePath: currentPath
          })
        }).then(() => {
          ssInstallCmd.push(
            `./ank.php --script=importConfiguration --glob=./${path.relative(
              srcPath,
              path.join(currentPath, "**", "*.xml")
            )}`
          );
          return currentPath;
        });
      })
      .then(currentPath => {
        if (withParameters) {
          return createTemplates.Parameters.writeTemplate(currentPath, {
            name
          }).then(() => {
            return currentPath;
          });
        }
        return currentPath;
      })
      .then(currentPath => {
        //Write the php if needed
        if (withBehavior) {
          return createTemplates.Behavior.writeTemplate(currentPath, {
            name,
            namespace: convertPathInPhpNamespace({
              vendorPath,
              smartStructurePath: currentPath
            }),
            parentName
          }).then(() => {
            return currentPath;
          });
        }
        return currentPath;
      })
      .then(currentPath => {
        // With settings
        if (withSettings) {
          return createTemplates.Settings.writeTemplate(currentPath, {
            name,
            workflow,
            withRender,
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
        //with render
        if (withRender) {
          //Generate the render access and two renders (one view, one edit)
          return createTemplates.Render.writeTemplate(currentPath, {
            name,
            namespace: convertPathInPhpNamespace({
              vendorPath,
              smartStructurePath: path.join(currentPath, "Render")
            })
          }).then(() => {
            return currentPath;
          });
        }
        return currentPath;
      })
      .then(currentPath => {
        // With autocompletion
        if (withAutocompletion) {
          return createTemplates.Autocompletion.writeTemplate(currentPath, {
            name,
            namespace: convertPathInPhpNamespace({
              vendorPath,
              smartStructurePath: path.join(currentPath, `${Name}Autocompletion`)
            })
          });
        }
        return currentPath;
      })
      .then(() => {
        //Complete the info.xml if needed
        if (insertIntoInfo) {
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

                postInstall[0].process.push(...ssInstallCmd.map(c => getProcessXml(c)));

                postUpgrade[0].process.push(...ssUpgradeCmd.map(c => getProcessXml(c)));
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
