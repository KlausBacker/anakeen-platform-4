const gulp = require("gulp");
const signale = require("signale");
const { getModuleInfo, getStructureFiles } = require("../utils/moduleInfo");
const treeify = require("treeify");

exports.getModuleInfo = ({ sourcePath, withStructure }) => {
  gulp.task("getModuleInfo", async () => {
    const info = await getModuleInfo(sourcePath);
    const keys = Object.keys(info.moduleInfo);
    keys.forEach(currentKey => {
      signale.info(currentKey, " : ", info.moduleInfo[currentKey]);
    });
    if (withStructure) {
      const buildPath = info.buildInfo.buildPath;
      const structureFiles = await getStructureFiles({ buildPath });
      //List structure and build the hierarchy
      const workingStructure = {};
      structureFiles.forEach(structureFile => {
        if (structureFile.structure) {
          Object.keys(structureFile.structure).forEach(currentStructureName => {
            const currentStructure =
              structureFile.structure[currentStructureName];
            let structureRef = workingStructure[currentStructure.name]
              ? workingStructure[currentStructure.name]
              : {};
            structureRef = { ...structureRef, ...currentStructure };
            workingStructure[currentStructure.name] = structureRef;

            if (currentStructure.extends) {
              if (!workingStructure[currentStructure.extends]) {
                workingStructure[currentStructure.extends] = {
                  name: currentStructure.extends
                };
              }
              if (!workingStructure[currentStructure.extends].children) {
                workingStructure[currentStructure.extends].children = {};
              }
              workingStructure[currentStructure.extends].children[
                structureRef.name
              ] = structureRef;
            }
          });
        }
      });
      //Clean structure of first level with an extend
      const finalStructure = {};
      Object.keys(workingStructure).forEach(structKey => {
        if (workingStructure[structKey].extends) {
          return;
        }
        finalStructure[structKey] = workingStructure[structKey];
      });
      signale.log("\n" + treeify.asTree(finalStructure, true));
    }
  });
};
