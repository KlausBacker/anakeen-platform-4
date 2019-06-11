#!/usr/bin/env node

const path = require("path");
const {checkFile} = require(path.resolve(__dirname,"../../../../index.js"));

try {
  const status = checkFile("./.cibuild/targets/autotest/build.xml");
  console.log(status);
  if (!status.ok) {
    process.exit(3);
  }
  console.log("Check done")
} catch (e) {
  console.error(e);
  process.exit(2);
}

