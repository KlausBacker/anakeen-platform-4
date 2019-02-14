const signale = require("signale");
const path = require("path");

const { Compose } = require(path.resolve(__dirname, "../../utils/Compose.js"));

exports.description = "Initialize a repository";

exports.builder = {
  localRepo: {
    description: "App repository output directory",
    default: "anakeen/repo",
    type: "string"
  },
  localSrc: {
    description: "Src packages output directory",
    default: "anakeen/src",
    type: "string"
  }
};

exports.handler = async argv => {
  signale.start(`Initializing 'repo.xml'...`);
  try {
    await new Compose().init(argv);
  } catch (e) {
    signale.error(`Error: ${e.message}`);
    throw e;
  }
  signale.success(`Done.`);
};
