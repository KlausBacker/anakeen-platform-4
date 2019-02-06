#!/usr/bin/env node

const fs = require("fs");
const util = require("util");
const { getModuleInfo } = require("@anakeen/anakeen-cli/utils/moduleInfo");

const readFile = util.promisify(fs.readFile);
const readPackage = async () => {
  const packageJson = await readFile("./package.json", { encoding: "utf8" });
  return JSON.parse(packageJson);
};

readPackage()
  .then(async content => {
    const info = await getModuleInfo("./");
    if (content.version !== info.moduleInfo.version) {
      console.error("The package.json and info.xml version must be ===");
      process.exit(42);
    }
  })
  .catch(error => {
    console.error(error);
    process.exit(42);
  });
