const gulp = require("gulp");
const { po } = require("../tasks/po");
const signale = require("signale");

signale.config({
  displayTimestamp: true,
  displayDate: true
});

exports.desc = "Extract the po of the module";
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

exports.handler = function(argv) {
  try {
    signale.time("po");
    po(argv);
    const task = gulp.task("extractPo");
    task()
      .then(() => {
        signale.timeEnd("po");
        signale.success("po done");
      })
      .catch(e => {
        signale.timeEnd("po");
        signale.error(e);
        process.exit(1);
      });
  } catch (e) {
    signale.timeEnd("po");
    signale.error(e);
    process.exit(1);
  }
};
