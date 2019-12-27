const signale = require("signale");
const path = require("path");

const { Compose } = require(path.resolve(__dirname, "../../utils/Compose.js"));

exports.description = "Install modules from 'repo.xml'";

exports.builder = {
  "frozen-lockfile": {
    descriptions: "Do not update lock file",
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
    const compose = await new Compose(argv);
    await compose.checkIfInitialized();
    await compose.install({});
  } catch (e) {
    signale.error(e);
    process.exit(1);
  }
  signale.success(`Done.`);
};
