#!/usr/bin/env node

const findUp = require("find-up");
const fs = require("fs");
const configPath = findUp.sync([".anakeen-cli", ".anakeen-cli.json"]);
const config = configPath ? JSON.parse(fs.readFileSync(configPath)) : {};

require("yargs")
  .config(config)
  .env('anakeen-cli')
  .commandDir("commands")
  .demandCommand()
  .help().argv;
