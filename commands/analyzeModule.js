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
  jsonReturn: {
    defaultDescription: "return in json",
    default: false,
    type: "boolean"
  }
};

exports.handler = async argv => {
  try {
    if (!argv.jsonReturn) {
      signale.time("moduleInfo");
    }
    getModuleInfo(argv);
    const task = gulp.task("getModuleInfo");
    task()
      .then(() => {
        if (!argv.jsonReturn) {
          signale.timeEnd("moduleInfo");
          signale.success("moduleInfo done");
        }
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
