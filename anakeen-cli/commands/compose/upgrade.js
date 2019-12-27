const signale = require("signale");
const path = require("path");

const { Compose } = require(path.resolve(__dirname, "../../utils/Compose.js"));

exports.description = "Upgrade modules from 'repo.xml'";

exports.builder = {
  moduleName: {
    alias: "m",
    description: "Module's name",
    type: "string",
    default: "all"
  },
  latest: {
    descriptions: "Upgrade module to latest version available",
    type: "boolean",
    default: false
  },
  debug: {
    alias: "d",
    descriptions: "Print debug messages",
    type: "boolean",
    default: false
  },
  cwd: {
    description: "working directory to use",
    type: "string",
    default: "."
  }
};

exports.handler = async argv => {
  signale.start(`Installing modules from 'repo.xml'...`);
  try {
    const compose = new Compose(argv);
    await compose.checkIfInitialized();
    if (argv.moduleName === "all") {
      //Upgrade all module, make an install without lockfile
      await compose.install({ withoutLockFile: true, latest: argv.latest });
    } else {
      await compose.addModule({
        moduleName: argv.moduleName,
        moduleVersion: argv.latest ? "latest" : "",
        registry: ""
      });
      await compose.install({});
    }
  } catch (e) {
    signale.error(e);
    process.exit(1);
  }
  signale.success(`Done.`);
};
