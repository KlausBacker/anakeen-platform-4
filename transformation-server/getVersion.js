/* eslint-disable */
const fs = require("fs");
const path = require("path");

console.log(
  JSON.parse(
    fs.readFileSync(path.resolve(__dirname, "tmp/package.json"), {
      encoding: "utf-8"
    })
  ).version
);
