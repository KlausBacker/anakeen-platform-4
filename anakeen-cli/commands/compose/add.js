const signale = require("signale");
const path = require("path");

const { Compose } = require(path.resolve(__dirname, "../../utils/Compose.js"));

exports.description = "Add an 'app' module dependency";

exports.builder = {
  moduleName: {
    alias: "m",
    description: "Module's name",
    type: "string",
    demandOption: true
  },
  moduleVersion: {
    alias: "v",
    description: "Module's semver version",
    type: "string",
    default: "latest"
  },
  registry: {
    alias: "r",
    description: "Registry's unique name/identifier from which the Module is to be downloaded",
    type: "string",
    default: ""
  },
  type: {
    alias: "t",
    description: "(dev)elopment or (prod)uction module",
    type: "string",
    default: "prod"
  },
  debug: {
    alias: "d",
    descriptions: "Print debug messages",
    type: "boolean",
    default: false
  },
  cwd: {
    description: "Working directory to use",
    type: "string",
    default: "."
  }
};

exports.handler = async argv => {
  try {
    const compose = await new Compose(argv);
    await compose.checkIfInitialized();
    if (argv.registry === "") {
      await compose.loadContext();
      const registry = await compose.repoXML.getRegistryList();
      if (registry.length === 0) {
        signale.error("There is no registry, you have to had one with addAppRegistry");
        process.exit(3);
      }
      if (registry.length === 1) {
        // concurrent modif is not possible is this case
        // eslint-disable-next-line require-atomic-updates
        argv.registry = registry[0].name;
      }
    }
    if (!argv.registry) {
      signale.error("You have to specify or add a registry");
      process.exit(2);
    }
    signale.start(
      `Adding 'app' module '${argv.moduleName}' with version '${argv.moduleVersion}' ` +
        `from registry '${argv.registry}'...`
    );
    await compose.addModule(argv);
    await compose.install(argv);
  } catch (e) {
    signale.error(e);
    process.exit(1);
  }
  signale.success(`Done.`);
};
