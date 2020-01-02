const signale = require("signale");
const path = require("path");

const { Compose } = require(path.resolve(__dirname, "../../utils/Compose.js"));

exports.description = "Initialize a repository";

exports.builder = {
  localRepo: {
    description: "App repository output directory",
    default: "repo",
    type: "string"
  },
  localSrc: {
    description: "Src packages output directory",
    default: "src",
    type: "string"
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
  signale.start(`Initializing 'repo.xml'...`);
  try {
    await new Compose(argv).init(argv);
  } catch (e) {
    signale.error(e);
    process.exit(1);
  }
  signale.success(`Done.`);
};
