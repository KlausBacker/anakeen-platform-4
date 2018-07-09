const gulp = require("gulp");
const { stub } = require("../tasks/stub");
const signale = require("signale");

signale.config({
  displayTimestamp: true,
  displayDate: true
});

exports.desc = "Generate the stubs";
exports.builder = {
  sourcePath: {
    defaultDescription: "path of the info.xml",
    alias: "s",
    default: ".",
    type: "string"
  },
  targetPath: {
    defaultDescription: "target path of stubs",
    alias: "t",
    default: "./stubs",
    type: "string"
  }
};

exports.handler = function(argv) {
  try {
    signale.time("stub");
    stub(argv);
    const task = gulp.task("stub");
    task()
      .then(() => {
        signale.timeEnd("stub");
        signale.success("stub done");
      })
      .catch(e => {
        signale.timeEnd("stub");
        signale.error(e);
      });
  } catch (e) {
    signale.timeEnd("stub");
    signale.error(e);
  }
};
