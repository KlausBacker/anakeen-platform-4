const gulp = require("gulp");
const { checkConfigFile } = require("../tasks/check");
const signale = require("signale");

signale.config({
  displayTimestamp: true,
  displayDate: true
});

exports.desc = "Check the xml of a configuration file";
exports.builder = {
  sourcePath: {
    description: "path of the configuration file",
    alias: "s",
    default: ".",
    type: "string",
    conflicts: "glob"
  },
  glob: {
    description: "glob instruction for the configuration files",
    alias: "g",
    type: "string",
    conflicts: "sourcePath"
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
