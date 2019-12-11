const signale = require("signale");

exports.command = "compose <command> [<options>]";

exports.description = "Compose a repository";

exports.builder = yargs => {
  return yargs
    .commandDir("compose")
    .command({
      command: "*",
      handler: argv => {
        if (argv._[0]) {
          signale.error("Unknown commmand compose", argv._[1]);
        } else {
          signale.error("You need to specify a command");
        }
        yargs.showHelp();
      }
    })
    .alias("h", "help")
    .showHelpOnFail(true);
};
