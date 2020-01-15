#!/usr/bin/env node

const { autoconf } = require("./utils/autoconf");
const yargs = require("yargs");
const path = require("path");
const { analyzePathForCommand } = require("./utils/completion");

(async () => {
  const config = (await autoconf()) || {};
  const commandList = await analyzePathForCommand(path.resolve(__dirname, "commands"), "", true);
  return yargs
    .config(config)
    .commandDir("commands")
    .alias("h", "help")
    .check(argv => {
      const currentCommand = argv._[0];
      if (commandList.indexOf(currentCommand) === -1) {
        throw new Error(`Unknown command ${argv._[0]}`);
      }
      return true;
    })
    .detectLocale(false)
    .version().argv;
})();
