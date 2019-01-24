#!/usr/bin/env node

const util = require("util");
const child_process = require("child_process");

const exec = util.promisify(child_process.exec);

if (process.env.CI_MERGE_REQUEST_TARGET_BRANCH_NAME === "master") {
  if (!process.env.CIBUILD_INTEGRATION_ANAKEEN_NPM_REGISTRY) {
    console.error("Unable to find the CI build env var");
    process.exit(2);
  }
  console.log(
    `npm config set @anakeen:registry ${
      process.env.CIBUILD_INTEGRATION_ANAKEEN_NPM_REGISTRY
    }`
  );
  exec(
    `npm config set @anakeen:registry ${
      process.env.CIBUILD_INTEGRATION_ANAKEEN_NPM_REGISTRY
    }`
  )
    .then(result => {
      console.log(result);
    })
    .catch(error => {
      console.error(error);
      process.exit(42);
    });
}
