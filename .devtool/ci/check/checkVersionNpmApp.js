#!/usr/bin/env node

const fs = require("fs");
const path = require("path");
const util = require("util");
const { getModuleInfo } = require("@anakeen/anakeen-cli/utils/moduleInfo");

const sourcePath = process.argv[2];

const readFile = util.promisify(fs.readFile);
const readPackage = async () => {
  const packageJson = await readFile(path.join(sourcePath, "./package.json"), { encoding: "utf8" });
  return JSON.parse(packageJson);
};

readPackage()
  .then(async content => {
    const info = await getModuleInfo(path.join(sourcePath, "./"));
    if (content.version !== info.moduleInfo.version) {
      console.error("The package.json and info.xml version must be ===");
      process.exit(42);
    }
  })
  .catch(error => {
    console.error(error);
    process.exit(42);
  });
