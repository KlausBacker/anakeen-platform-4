#!/usr/bin/env node

const usage = `
Usage
-----

    ${process.argv[1]} <file>

`;

const path = require("path");
const indexFile = path.resolve(__dirname, "index.js");
const validator = require(indexFile);

if (process.argv.length <= 2) {
  console.error(usage);
  throw new Error(`Missing file argument!`);
}

let error = false;
for (let i = 2; i < process.argv.length; i++) {
  let file = process.argv[i];
  let result = validator.checkFile(file);
  if (result.ok) {
    console.log(`'${file}': [OK]`);
  } else if (result.ignore) {
    console.log(`'${file}': [IGNORED]`);
  } else {
    console.log(`'${file}': [ERROR] (Error: ${result.error})`);
    error = true;
  }
}
if (error) {
  console.log("");
  process.exit(1);
}
