#!/usr/bin/env node

const fs = require("fs");
const util = require("util");
const child_process = require("child_process");

const readFile = util.promisify(fs.readFile);
const exec = util.promisify(child_process.exec);

const readPackage = async () => {
  const packageJson = await readFile("./package.json", { encoding: "utf8" });
  return JSON.parse(packageJson);
};

readPackage()
  .then(async content => {
    const {stderr, stdout} = await exec(`yarn --json info ${content.name} versions`);
    const versions = JSON.parse(stdout);
    if (versions.data.indexOf(content.version) !== -1) {
      console.error("This version already exist in the npm");
      process.exit(1);
    }
    console.log("OK");
  })
  .catch(error => {
    console.error(error);
    process.exit(42);
  });