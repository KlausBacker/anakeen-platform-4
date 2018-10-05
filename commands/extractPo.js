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
    defaultDescription: "path of the info.xml",
    alias: "s",
    default: ".",
    type: "string"
  }
};

exports.handler = function(argv) {
  try {
    signale.time("po");
    po(argv);
    const task = gulp.task("poSmart");
    task()
      .then(() => {
        signale.timeEnd("po");
        signale.success("po done");
      })
      .catch(e => {
        signale.timeEnd("po");
        signale.error(e);
      });
  } catch (e) {
    signale.timeEnd("po");
    signale.error(e);
  }
};
