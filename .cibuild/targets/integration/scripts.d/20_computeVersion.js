#!/usr/bin/env node

const util = require("util");
const child_process = require("child_process");
const { getInfoFromApp, findApp } = require("@anakeen/anakeen-ci/utils");
const exec = util.promisify(child_process.exec);


findApp("user-interfaces").then(async(appPath) => {
  const appInfo = await getInfoFromApp(appPath);
  console.log("Bump autorelease");
  await exec(
    `yarn version --no-git-tag-version --no-commit-hooks --new-version ${appInfo.version}`
  );
}).then(() => {
  console.log("OK");
})
  .catch(err => {
    console.error(err);
    process.exit(42);
  });