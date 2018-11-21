const gulp = require("gulp");
const signale = require("signale");
const { getModuleInfo } = require("../utils/moduleInfo");

exports.getModuleInfo = ({ sourcePath, jsonReturn }) => {
  gulp.task("getModuleInfo", async () => {
    const info = await getModuleInfo(sourcePath);
    if (jsonReturn) {
      return info;
    }
    const keys = Object.keys(info.moduleInfo);
    keys.forEach(currentKey => {
      signale.info(currentKey, " : ", info.moduleInfo[currentKey]);
    });
  });
};
