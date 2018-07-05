const gulp = require("gulp");
const signale = require("signale");
const { build } = require("../tasks/build");

signale.config({
  displayTimestamp: true,
  displayDate: true
});

exports.desc = "Build the app file";
exports.builder = {
  sourcePath: {
    defaultDescription: "path of the info.xml",
    alias: "s",
    default: ".",
    type: "string"
  },
  targetPath: {
    defaultDescription: "target path",
    alias: "t",
    default: ".",
    type: "string"
  },
  autoRelease: {
    defaultDescription: "add current timestamp to the release",
    default: false,
    type: "boolean"
  }
};

exports.handler = function(argv) {
  try {
    signale.time("build");
    build(argv);
    const task = gulp.task("build");
    task()
      .then(() => {
        signale.timeEnd("build");
        signale.success("build done");
      })
      .catch(e => {
        signale.timeEnd("build");
        signale.error(e);
      });
  } catch (e) {
    signale.timeEnd("build");
    signale.error(e);
  }
};
