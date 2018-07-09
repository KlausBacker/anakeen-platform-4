const gulp = require("gulp");
const signale = require("signale");
const { getModuleInfo } = require("../tasks/moduleInfo");

exports.desc = "Analyze the module content";
exports.builder = {
  sourcePath: {
    defaultDescription: "path of the info.xml",
    alias: "s",
    default: ".",
    type: "string"
  },
  withStructure: {
    defaultDescription: "print the structure hierarchy",
    default: false,
    type: "boolean"
  }
};

exports.handler = async argv => {
  try {
    signale.time("moduleInfo");
    getModuleInfo(argv);
    const task = gulp.task("getModuleInfo");
    task()
      .then(() => {
        signale.timeEnd("moduleInfo");
        signale.success("moduleInfo done");
      })
      .catch(e => {
        signale.timeEnd("moduleInfo");
        signale.error(e);
      });
  } catch (e) {
    signale.timeEnd("moduleInfo");
    signale.error(e);
  }
};
