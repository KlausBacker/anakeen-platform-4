const gulp = require("gulp");
const { getModuleInfo } = require("../utils/moduleInfo");
const fs = require("fs");
const fsUtils = require("./plugins/files");
const path = require("path");
const { generateSetting } = require("./createSetting/index.js");

exports.createSetting = ({
  sourcePath,
  name,
  vendorName,
  moduleName,
  settingPath,
  associatedSmartStructure,
  associatedWorkflow,
  type,
  inSelfDirectory = true,
  insertIntoInfo = true
}) => {
  return gulp.task("createSetting", async () => {
    //Get module info
    const moduleInfo = await getModuleInfo(sourcePath);
    //Check the path for the xml file
    if (!vendorName) {
      vendorName = moduleInfo.moduleInfo.vendor;
    }
    if (!moduleName) {
      moduleName = moduleInfo.moduleInfo.name;
    }
    let srcPath;
    if (!settingPath && !vendorName) {
      throw new Error("You need to specify a vendor name or settingPath");
    }
    if (!settingPath && vendorName && moduleName) {
      let basePath = path.join("vendor", vendorName, moduleName);

      //Compute and test the settingPath for the vendor name
      srcPath = moduleInfo.buildInfo.buildPath.find(currentPath => {
        //Check current path
        const smartPath = path.join(currentPath, basePath);
        try {
          return fs.exists(smartPath) && fs.statSync(smartPath).isDirectory();
        } catch (e) {
          return smartPath;
        }
      });
      if (associatedSmartStructure) {
        const StructureName =
          associatedSmartStructure.charAt(0).toUpperCase() + associatedSmartStructure.slice(1).toLowerCase();
        basePath = path.join(
          "vendor",
          vendorName,
          moduleName,
          "SmartStructures",
          StructureName,
          `${StructureName}Settings`
        );
      }
      if (!srcPath) {
        let errorMessage = `Unable to find a setting path for the vendor (${vendorName}), you should create it or indicate the settingPath option`;
        // if (associatedSmartStructure) {
        //   errorMessage = `Unable to find a setting path for the vendor (${vendorName}) and the structure ${associatedSmartStructure}, you should create it or indicate the settingPath option`;
        // }
        fsUtils.mkpdir(basePath, err => {
          if (err) {
            errorMessage = err;
          }
        });
        throw new Error(errorMessage);
      }
      settingPath = path.join(srcPath, basePath);
    }
    //Create the directory if needed
    let directoryPromise = Promise.resolve(settingPath);
    const Name = name.charAt(0).toUpperCase() + name.slice(1);
    if (inSelfDirectory) {
      const settingDirectory = path.join(settingPath, Name);
      directoryPromise = new Promise((resolve, reject) => {
        fsUtils.mkpdir(settingDirectory, err => {
          if (err) {
            reject(err);
          }
          resolve(settingDirectory);
        });
      });
    }
    return directoryPromise
      .then(currentPath => {
        return generateSetting(currentPath, {
          sourcePath,
          name,
          vendorName,
          moduleName,
          settingPath,
          associatedSmartStructure,
          associatedWorkflow,
          type,
          inSelfDirectory,
          insertIntoInfo
        });
      })
      .then(() => {
        return Promise.resolve();
      });
  });
};
