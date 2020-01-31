const signale = require("signale");
const path = require("path");

const { Compose } = require(path.resolve(__dirname, "../../utils/Compose.js"));

exports.description = "Generate an installer with current app repo";

exports.builder = {
  localRepoName: {
    description: "Path of the local repo in the zip",
    default: "control/localRepo",
    type: "string"
  },
  controlTarget: {
    alias: "t",
    description: "Path where to generate the control",
    default: "./control.zip",
    type: "string"
  },
  customReadme: {
    description: "Path to a custom mustache readme the path value will be updated",
    type: "string",
    default: ""
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
    await compose.generateLocalControl(argv);
  } catch (e) {
    signale.error(e);
    process.exit(1);
  }
  signale.success(`Done.`);
};
