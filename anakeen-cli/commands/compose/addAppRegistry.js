const signale = require("signale");
const path = require("path");

const { Compose } = require(path.resolve(__dirname, "../../utils/Compose.js"));

exports.description = "Add an 'app' registry";

exports.builder = {
  name: {
    description: "Registry's unique name/identifier",
    type: "string",
    demandOption: true
  },
  url: {
    description: "Registry's base URL (e.g. 'http://localhost:8080')",
    type: "string",
    demandOption: true
  },
  authUser: {
    description: "Registry's authentication username",
    default: null,
    type: "string"
  },
  authPassword: {
    description: "Registry's authentication password",
    default: null,
    type: "string"
  }
};

exports.handler = async argv => {
  signale.start(`Adding registry '${argv.name}' with URL '${argv.url}'...`);
  try {
    await new Compose().addAppRegistry(argv);
  } catch (e) {
    signale.error(e);
    process.exit(1);
  }
  signale.success(`Done.`);
};
