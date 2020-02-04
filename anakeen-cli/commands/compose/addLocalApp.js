const signale = require("signale");
const path = require("path");

const { Compose } = require(path.resolve(__dirname, "../../utils/Compose.js"));

exports.description = "Add a local glob with app";

exports.builder = {
  localPath: {
    alias: "p",
    description: "Glob with the local app",
    type: "string",
    demandOption: true
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
  try {
    const compose = new Compose(argv);
    await compose.checkIfInitialized();
    await compose.addLocalPath(argv);
  } catch (e) {
    signale.error(e);
    process.exit(1);
  }
  signale.success(`Done.`);
};
