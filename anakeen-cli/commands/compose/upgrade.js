const signale = require("signale");
const path = require("path");

const { Compose } = require(path.resolve(__dirname, "../../utils/Compose.js"));

exports.description = "Install modules from 'repo.xml'";

exports.builder = {
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
    await new Compose(argv).checkIfInitialized().upgrade(argv._.slice(2));
  } catch (e) {
    signale.error(e);
    process.exit(1);
  }
  signale.success(`Done.`);
};
