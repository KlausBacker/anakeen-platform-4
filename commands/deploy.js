const { deploy, buildAndDeploy } = require("../tasks/deploy");
const signale = require("signale");
const { controlArguments } = require("../utils/control");

signale.config({
  displayTimestamp: true,
  displayDate: true
});

exports.desc = "Deploy the app file";
exports.builder = controlArguments({
  appPath: {
    defaultDescription: "application file path",
    alias: "t",
    demandOption:
      "You must give the path of .app to deploy or the path of the source for autorelease",
    type: "string"
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
    type: "boolean"
  }
});

exports.handler = function(argv) {
  try {
    signale.time("deploy");
    let task;
    if (argv.autoRelease) {
      signale.info("autorelease mode");
      task = buildAndDeploy({ sourcePath: argv.appPath, ...argv }).tasks
        .buildAndDeploy.fn;
    } else {
      signale.info("app deploy mode " + argv.appPath);
      task = deploy(argv).tasks.deploy.fn;
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
