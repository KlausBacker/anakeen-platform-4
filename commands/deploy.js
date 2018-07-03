const gulp = require('gulp');
const { deploy, buildAndDeploy } = require("../tasks/deploy");
const signale = require("signale");
const { controlArguments } = require("../utils/control");
const fs = require("fs");

signale.config({
  displayTimestamp: true,
  displayDate: true
});

exports.desc = "Deploy the app file";
exports.builder = controlArguments({
  appPath: {
    defaultDescription: "application file path",
    alias: "t",
    type: "string",
    coerce: (arg) => {
      if (!fs.statSync(arg).isFile()) {
        throw new Error("Unable to find the file "+ arg);
      }
      return arg;
    }
  },
  sourcePath: {
    defaultDescription: "path of the info.xml for the autorelease mode",
    alias: "s",
    type: "string",
    coerce: (arg) => {
      if (!fs.statSync(arg).isDirectory()) {
        throw new Error("Unable to find the source directory "+ arg);
      }
      return arg;
    }
  },
  force: {
    defaultDescription: "destroy already existing deployment",
    alias: "f",
    default: false,
    type: "boolean"
  },
  autoRelease: {
    defaultDescription: "add current timestamp to the release",
    default: false,
    type: "boolean",
    implies: "sourcePath"
  }
});

exports.handler = function(argv) {
  try {
    signale.time("deploy");
    let task;
    if (argv.sourcePath) {
      signale.info("source mode");
      buildAndDeploy(argv);
      task = gulp.task("buildAndDeploy");
    } else {
      signale.info("app deploy mode " + argv.appPath);
      deploy(argv);
      task = gulp.task("deploy");
    }
    task()
      .then(() => {
        signale.timeEnd("deploy");
        signale.success("deploy done");
      })
      .catch(e => {
        signale.timeEnd("deploy");
        signale.error(e);
      });
  } catch (e) {
    signale.timeEnd("deploy");
    signale.error(e);
  }
};
