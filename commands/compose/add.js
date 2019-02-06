const signale = require("signale");
const path = require("path");

const { Compose } = require(path.resolve(__dirname, "../../utils/Compose.js"));

exports.description = "Add an 'app' module dependency";

exports.builder = {
  name: {
    description: "Module's name",
    type: "string",
    requiresArg: true
  },
  version: {
    description: "Module's semver version",
    type: "string",
    default: "latest"
  },
  registry: {
    description:
      "Registry's unique name/identifier from which the Module is to be downloaded",
    type: "string",
    requiresArg: true
  }
};

exports.handler = async argv => {
  signale.start(
    `Adding 'app' module '${argv.name}' with version '${argv.version}' ` +
      `from registry '${argv.registry}'...`
  );
  try {
    await Compose.addModule(argv);
  } catch (e) {
    signale.error(`Error: ${e.message}`);
    throw e;
  }
  signale.success(`Done.`);
};
