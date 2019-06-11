const gulp = require("gulp");
const { check } = require("../tasks/check");
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
      signale.time("check");
    }
    check(argv);
    const task = gulp.task("check");
    return task()
      .then(() => {
        if (silent !== true) {
          signale.timeEnd("check");
          signale.success("check done");
        }
      })
      .catch(e => {
        if (silent !== true) {
          signale.timeEnd("check");
          signale.error(e);
        } else {
          return Promise.reject(e);
        }
      });
  } catch (e) {
    if (silent !== true) {
      signale.timeEnd("check");
      signale.error(e);
    } else {
      return Promise.reject(e);
    }
  }
};
