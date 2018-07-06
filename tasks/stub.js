const gulp = require("gulp");
const { parseStub } = require("../utils/STUBGenerator");
const asyncCallback = require("./plugins/asyncCallback");
const { getModuleInfo, getStructureFiles } = require("../utils/moduleInfo");

exports.stub = ({ sourcePath, targetPath = "./stubs" }) => {
  return gulp.task("stub", () => {
    return new Promise(async (resolve, reject) => {
      if (sourcePath === undefined) {
        throw new Error("No source path specified.");
      }
      try {
        const info = await getModuleInfo(sourcePath);
        const buildPath = info.buildInfo.buildPath;
        const structureFiles = await getStructureFiles({ buildPath });
        const files = structureFiles.map(currentStruct => {
          return currentStruct.path;
        });

        gulp
          .src(files)
          .pipe(asyncCallback(parseStub, true))
          .pipe(gulp.dest(targetPath))
          .on("end", resolve)
          .on("error", reject);
      } catch (e) {
        reject(e);
      }
    });
  });
};
