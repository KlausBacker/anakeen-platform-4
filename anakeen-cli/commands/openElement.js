const gulp = require("gulp");
const signale = require("signale");
const fs = require("fs");

const { openElement } = require("../tasks/openElement");

signale.config({
  displayTimestamp: true,
  displayDate: true
});

exports.desc = "open an element to the correct devel center page";
exports.builder = {
  filePath: {
    description: "path of the element to open",
    alias: "f",
    type: "string",
    coerce: arg => {
      if (!fs.statSync(arg)) {
        throw new Error("Unable to find the source directory " + arg);
      }
      return arg;
    },
    required: true
  },
  lineNumber: {
    alias: "l",
    type: "number",
    required: true
  },
  columnNumber: {
    alias: "c",
    type: "number",
    required: true
  },
  contextUrl: {
    description: "url of the context",
    alias: "C",
    type: "string",
    required: true
  }
};

exports.handler = function(argv) {
  try {
    if (argv.name) {
      signale.info(`opening ${argv.name}`);
    }
    openElement(argv);
    const task = gulp.task("openElement");
    return task().then(() => {
      signale.timeEnd("openElement");
    });
  } catch (e) {
    signale.timeEnd("openElement");
    signale.error(e);
  }
};
