const gulp = require("gulp");
const { deploy, buildAndDeploy } = require("../tasks/deploy");
const signale = require("signale");
const { controlArguments } = require("../utils/control");
const fs = require("fs");
const check = require("./check");

signale.config({
  displayTimestamp: true,
  displayDate: true
});

exports.desc = "Deploy the app file";
exports.builder = controlArguments({
  appPath: {
    description: "application file path",
    alias: "t",
    type: "string",
    coerce: arg => {
      if (!fs.statSync(arg).isFile()) {
        throw new Error("Unable to find the file " + arg);
      }
      return arg;
    }
  },
  sourcePath: {
    description: "path of the info.xml for the autorelease mode",
    alias: "s",
    type: "string",
    coerce: arg => {
      if (!fs.statSync(arg).isDirectory()) {
        throw new Error("Unable to find the source directory " + arg);
      }
      return arg;
    }
  },
  autoRelease: {
    description: "prefix for version minor part. Add current timestamp",
    default: "dev",
    type: "string",
    implies: "sourcePath"
  },
  action: {
    description: "action to execute (install|upgrade)",
    default: "",
    type: "string"
  }
});

exports.handler = function(argv) {
  try {
    let checkPromise = Promise.resolve();
    signale.time("deploy");
    let task;
    if (argv.sourcePath) {
      signale.info("source mode");
      if (!argv.noCheck) {
        checkPromise = check.handler(argv, true);
      }
      buildAndDeploy(argv);
      task = gulp.task("buildAndDeploy");
    } else {
      signale.info("app deploy mode " + argv.appPath);
      deploy(argv);
      task = gulp.task("deploy");
    }
    checkPromise
      .then(() => {
        task()
          .then(() => {
            signale.timeEnd("deploy");
            signale.success("Deploy module succeeded");
          })
          .catch(e => {
            signale.timeEnd("deploy");
            if (e.message) {
              signale.error(e.message);
            } else {
              signale.error(e);
            }
            process.exit(1);
          });
      })
      .catch(e => {
        signale.timeEnd("deploy");
        signale.error(e);
        process.exit(1);
      });
  } catch (e) {
    signale.timeEnd("deploy");
    signale.error(e);
    process.exit(1);
  }
};
