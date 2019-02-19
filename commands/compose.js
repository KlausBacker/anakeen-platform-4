exports.command = "compose <command> [<options>]";

exports.description = "Compose a repository";

exports.builder = yargs => {
  return yargs.commandDir("compose");
};
