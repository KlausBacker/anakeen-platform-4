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
    if (content.autorelease) {
      //Autorelease mode, so we change the release
      let dNow = new Date()
        .toISOString()
        .replace(/[^0-9]/g, "")
        .substr(0, 14);
      const versionAutorelease = `${content.version}-dev${dNow}`;
      console.log("Bump autorelease");
      await exec(
        `yarn version --no-git-tag-version --no-commit-hooks --new-version ${versionAutorelease}`
      );
    }
  })
  .catch(error => {
    console.error(error);
    process.exit(42);
  });
