const signale = require("signale");
const path = require("path");

const { Compose } = require(path.resolve(__dirname, "../../utils/Compose.js"));

exports.description = "Install modules from 'repo.xml'";

exports.builder = {
  debug: {
    descriptions: "Print debug messages",
    type: "boolean",
    default: false
  },
  "frozen-lockfile": {
    descriptions: "Do not update lock file",
    type: "boolean",
    default: false
  }
};

exports.handler = async argv => {
  signale.start(`Installing modules from 'repo.xml'...`);
  try {
    await new Compose({
      debug: argv.debug,
      frozenLockfile: argv["frozen-lockfile"]
    }).install(argv);
  } catch (e) {
    signale.error(e);
    process.exit(1);
  }
  signale.success(`Done.`);
};
