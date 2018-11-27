const gulp = require("gulp");
const signale = require("signale");
const { getModuleInfo } = require("../tasks/moduleInfo");

exports.desc = "Analyze the module content";
exports.builder = {
  sourcePath: {
    description: "path of the info.xml",
    alias: "s",
    default: ".",
    type: "string"
  },
  jsonReturn: {
    description: "return in json",
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
      .then(info => {
        if (!argv.jsonReturn) {
          signale.timeEnd("moduleInfo");
          signale.success("moduleInfo done");
        } else {
          // eslint-disable-next-line no-console
          console.log(info);
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
