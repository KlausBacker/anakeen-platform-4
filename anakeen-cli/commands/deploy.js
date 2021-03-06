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
    },
    conflicts: ["sourcePath"]
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
    },
    conflicts: ["appPath"]
  },
  autoRelease: {
    description: "prefix for version minor part. Add current timestamp",
    type: "string",
    implies: "sourcePath"
  },
  reinstall: {
    description: "force reinstall instead if module is already installed",
    default: "",
    type: "boolean"
  }
});

exports.handler = function(argv) {
  try {
    let checkPromise = Promise.resolve();
    signale.time("deploy");
    let task;
    if (argv.sourcePath && !argv.appPath) {
      signale.info("source mode");
      if (!argv.noCheck) {
        checkPromise = check.handler(argv, true);
      }
      buildAndDeploy(argv);
      task = gulp.task("buildAndDeploy");
    } else if (argv.appPath && !argv.sourcePath) {
      signale.info("app deploy mode " + argv.appPath);
      deploy(argv);
      task = gulp.task("deploy");
    } else {
      signale.error("You must use either '--appPath' or '--sourcePath' option");
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
              signale.error(JSON.stringify(e.message));
            } else {
              signale.error(JSON.stringify(e));
            }
            process.exit(1);
          });
      })
      .catch(e => {
        signale.timeEnd("deploy");
        signale.error(JSON.stringify(e));
        process.exit(1);
      });
  } catch (e) {
    signale.timeEnd("deploy");
    signale.error(JSON.stringify(e));
    process.exit(1);
  }
};
