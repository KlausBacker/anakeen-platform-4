#!/usr/bin/env node

const { autoconf } = require("./utils/autoconf");
const signale = require("signale");
const yargs = require("yargs");

(async () => {
  const config = (await autoconf()) || {};
  return yargs
    .config(config)
    .commandDir("commands")
    .command({
      command: "*",
      handler: argv => {
        if (argv._[0]) {
          signale.error("Unknown commmand", argv._[0]);
        } else {
          signale.error("You need to specify a command");
        }
        yargs.showHelp();
      }
    })
    .alias("h", "help")
    .showHelpOnFail(true)
    .detectLocale(false)
    .help().argv;
})();
