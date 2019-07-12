const fs = require("fs");
const path = require("path");

console.log(
  JSON.parse(
    fs.readFileSync(path.resolve(__dirname, "./src/version.json"), {
      encoding: "utf-8"
    })
  ).version
);
