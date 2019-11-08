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
    description: "path of the info.xml",
    alias: "s",
    default: ".",
    type: "string"
  },
  targetPath: {
    description: "target path of stubs",
    alias: "t",
    default: "./stubs",
    type: "string"
  },
  verbose: {
    description: "verbose mode",
    alias: "v",
    default: false,
    type: "boolean"
  }
};

exports.handler = function(argv) {
  try {
    signale.time("stub");
    stub(argv);
    const task = gulp.task("allStubs");
    task()
      .then(() => {
        signale.timeEnd("stub");
        signale.success("stub done");
      })
      .catch(e => {
        signale.timeEnd("stub");
        signale.error(e);
        process.exit(1);
      });
  } catch (e) {
    signale.timeEnd("stub");
    signale.error(e);
    process.exit(1);
  }
};
