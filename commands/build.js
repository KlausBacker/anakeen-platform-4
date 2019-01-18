const gulp = require("gulp");
const signale = require("signale");
const { build } = require("../tasks/build");
const check = require("./check");

signale.config({
  displayTimestamp: true,
  displayDate: true
});

exports.desc = "Build the app file";
exports.builder = {
  sourcePath: {
    description: "path of the info.xml",
    alias: "s",
    default: ".",
    type: "string"
  },
  targetPath: {
    description: "target path",
    alias: "t",
    default: ".",
    type: "string"
  },
  autoRelease: {
    description: "prefix for version minor part. Add current timestamp",
    default: false,
    type: "string"
  },
  noCheck: {
    description: "add check of XML inside the module",
    default: false,
    type: "boolean"
  }
};

exports.handler = function(argv) {
  try {
    let checkPromise = Promise.resolve();
    if (!argv.noCheck) {
      checkPromise = check.handler(argv, true);
    }
    signale.time("build");
    checkPromise
      .then(() => {
        build(argv);
        const task = gulp.task("build");
        task().then(() => {
          signale.timeEnd("build");
          signale.success("build done");
        });
      })
      .catch(e => {
        signale.timeEnd("build");
        signale.error(e);
      });
  } catch (e) {
    signale.error(e);
  }
};
