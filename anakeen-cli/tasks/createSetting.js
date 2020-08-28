const gulp = require("gulp");
const { getModuleInfo } = require("../utils/moduleInfo");
const fs = require("fs");
const fsUtils = require("./plugins/files");
const path = require("path");
const { generateSetting } = require("./createSetting/index.js");
const camelCase = require("camelcase");

exports.createSetting = ({
  sourcePath,
  name,
  vendorName,
  moduleName,
  settingPath,
  associatedSmartStructure,
  associatedWorkflow,
  type,
  settingFileName,
  insertIntoInfo = true,
  force
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

    let modulePath = path.join("vendor", vendorName, moduleName);
    if (!settingPath && vendorName && moduleName) {
      //Compute and test the settingPath for the vendor name

      srcPath = moduleInfo.buildInfo.buildPath.find(currentPath => {
        //Check current path
        return path.join(currentPath, modulePath);
      });

      if (srcPath && (!fs.existsSync(srcPath) || !fs.statSync(srcPath).isDirectory())) {
        throw new Error(`Source directory "${srcPath}" not exists. Use "settingPath" path option or create directory`);
      } else {
        settingPath = path.join(srcPath, modulePath);
        if (!fs.existsSync(settingPath) || !fs.statSync(settingPath).isDirectory()) {
          throw new Error(
            `Module directory "${settingPath}" not exists. Use "settingPath" path option or create directory`
          );
        }
      }
      let basePath = modulePath;
      if (associatedSmartStructure) {
        const StructureName = camelCase(associatedSmartStructure, { pascalCase: true });

        basePath = path.join(modulePath, "SmartStructures", StructureName, `${StructureName}Settings`);
      }

      settingPath = path.join(srcPath, basePath);
    }
    let directoryPromise = Promise.resolve(settingPath);
    const Name = camelCase(name, { pascalCase: true });
    if (!settingFileName) {
      // set default value of settingFileName to the name of the setting
      settingFileName = camelCase(Name, { pascalCase: true });
    } else {
      // set value by user's value
      settingFileName = camelCase(settingFileName, { pascalCase: true });
    }
    // create the directory as needed
    directoryPromise = new Promise((resolve, reject) => {
      fsUtils.mkpdir(settingPath, err => {
        if (err) {
          reject(err);
        }
        resolve(settingPath);
      });
    });
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
          settingFileName,
          insertIntoInfo,
          force
        });
      })
      .then(() => {
        return Promise.resolve();
      });
  });
};
