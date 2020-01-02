const signale = require("signale");
const path = require("path");

const { Compose } = require(path.resolve(__dirname, "../../utils/Compose.js"));

exports.description = "Add an 'app' registry";

exports.builder = {
  registryName: {
    alias: "n",
    description: "Registry's unique name/identifier",
    type: "string",
    demandOption: true
  },
  registryUrl: {
    alias: "url",
    description: "Registry's base URL (e.g. 'http://localhost:8080')",
    type: "string",
    demandOption: true
  },
  registryUser: {
    alias: "u",
    description: "Registry's authentication username",
    default: null,
    type: "string"
  },
  registryPassword: {
    alias: "p",
    description: "Registry's authentication password",
    default: null,
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
  signale.start(`Adding registry '${argv.registryName}' with URL '${argv.registryUrl}'...`);
  try {
    const compose = new Compose(argv);
    await compose.checkIfInitialized();
    await compose.checkRegistry({
      name: argv.registryName,
      url: argv.registryUrl,
      authUser: argv.registryUser,
      authPassword: argv.registryPassword
    });
    await compose.addAppRegistry({
      name: argv.registryName,
      url: argv.registryUrl,
      authUser: argv.registryUser,
      authPassword: argv.registryPassword
    });
  } catch (e) {
    signale.error(e);
    process.exit(1);
  }
  signale.success(`Done.`);
};
