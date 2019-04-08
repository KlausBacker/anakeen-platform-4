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
  process.stderr.write(`Error: missing file argument!\n${usage}\n`);
  process.exit(1);
}

let error = false;
for (let i = 2; i < process.argv.length; i++) {
  let file = process.argv[i];
  let result = validator.checkFile(file);
  if (result.ok) {
    process.stdout.write(`'${file}': [OK]\n`);
  } else if (result.ignore) {
    process.stdout.write(`'${file}': [IGNORED]\n`);
  } else {
    process.stdout.write(`'${file}': [ERROR] (Error: ${result.error})\n`);
    error = true;
  }
}
if (error) {
  process.stdout.write("\n");
  process.exit(1);
}
