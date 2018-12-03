const gulp = require("gulp");
const { checkConfigFile } = require("../tasks/check");
const signale = require("signale");

signale.config({
  displayTimestamp: true,
  displayDate: true
});

exports.desc = "Check the xml of the module";
exports.builder = {
  sourcePath: {
    description: "path of the info.xml",
    alias: "s",
    default: ".",
    type: "string"
  },
  verbose: {
    description: "verbose mode",
    alias: "v",
    default: false,
    type: "boolean"
  }
};

exports.handler = function(argv, silent) {
  try {
    if (silent !== true) {
      signale.time("checkConfigFile");
    }
    checkConfigFile(argv);
    const task = gulp.task("checkConfigFile");
    return task()
      .then(() => {
        if (silent !== true) {
          signale.timeEnd("checkConfigFile");
          signale.success("check configuration file done");
        }
      })
      .catch(e => {
        if (silent !== true) {
          signale.timeEnd("checkConfigFile");
          signale.error(e);
        } else {
          return Promise.reject(e);
        }
      });
  } catch (e) {
    if (silent !== true) {
      signale.timeEnd("checkConfigFile");
      signale.error(e);
    } else {
      return Promise.reject(e);
    }
  }
};
