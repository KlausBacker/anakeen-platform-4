const gulp = require("gulp");
const { deployConfiguration } = require("../tasks/deployConfiguration");
const signale = require("signale");
const fs = require("fs");
const checkConfigFile = require("./checkConfigFile");

signale.config({
  displayTimestamp: true,
  displayDate: true
});

exports.desc = "Import a configuration";
exports.builder = {
  noCheck: {
    description: "add check of XML inside the module",
    default: false,
    type: "boolean"
  },
  verbose: {
    description: "verbose display",
    alias: "v",
    default: false,
    type: "boolean"
  },
  dryRun: {
    description: "make dry run import",
    alias: "d",
    default: false,
    type: "boolean"
  },
  sourcePath: {
    description: "path of the xml configuration to import",
    alias: "s",
    type: "string",
    coerce: arg => {
      if (!fs.statSync(arg).isFile()) {
        throw new Error("Unable to find the source file " + arg);
      }
      return arg;
    }
  },
  contextUrl: {
    description: "url of the context",
    alias: "c",
    type: "string",
    required: true
  },
  username: {
    alias: "u",
    type: "string",
    required: true
  },
  password: {
    alias: "p",
    type: "string",
    required: true
  }
};

exports.handler = function(argv) {
  try {
    let checkPromise = Promise.resolve();
    signale.time("importConfiguration");
    if (argv.dryRun) {
      signale.info("Dry run import mode");
    }
    if (argv.verbose) {
      signale.info("Verbose mode");
    }
    if (!argv.noCheck) {
      checkPromise = checkConfigFile.handler(argv, true);
    }
    deployConfiguration(argv);
    const task = gulp.task("importConfiguration");
    checkPromise
      .then(() => {
        return task().then(() => {
          signale.timeEnd("importConfiguration");
          signale.success("import configuration done");
        });
      })
      .catch(e => {
        signale.timeEnd("importConfiguration");
        signale.error(e);
      });
  } catch (e) {
    signale.timeEnd("importConfiguration");
    signale.error(e);
  }
};
