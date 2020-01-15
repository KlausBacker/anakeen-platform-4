#!/usr/bin/env node

const yargs = require("yargs");
const path = require("path");
const { analyzePathForCommand, analyzeFileForCommand } = require("./utils/completion");

(async () => {
  return yargs.completion("completion", "generate completion script", async (current, argv) => {
    //No first element, return all the command
    const baseCommand = argv._[2];
    if (!baseCommand) {
      return await analyzePathForCommand(path.resolve(__dirname, "commands"));
    }
    //if base command is compose, handle it
    if (baseCommand === "compose") {
      const subCommand = argv._[3];
      if (!subCommand) {
        return await analyzePathForCommand(path.resolve(__dirname, "commands", "compose"));
      }
      try {
        return await analyzeFileForCommand(path.resolve(__dirname, "commands", baseCommand, `${subCommand}.js`));
      } catch (e) {
        //If the file is not found maybe, we just need to reduce the option
        if (argv._.length === 3) {
          return await analyzePathForCommand(path.resolve(__dirname, "commands", "compose"), argv._[3]);
        }
      }
    }
    //Open first element, search and analyze it
    try {
      return await analyzeFileForCommand(path.resolve(__dirname, "commands", `${baseCommand}.js`));
    } catch (e) {
      if (argv._.length === 2) {
        //If the file is not found maybe, we just need to reduce the option
        return await analyzePathForCommand(path.resolve(__dirname, "commands"), argv._[2]);
      }
    }
  }).argv;
})();
