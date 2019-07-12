//autorelease script for npm only package
//take the version and add the day date

const fs = require("fs");
const path = require("path");
const util = require("util");

const sourcePath = process.argv[2];
let versionAutorelease = process.argv[3] || "COMPUTE";
const versionFile = process.argv[4] || "./package.json";

const readFile = util.promisify(fs.readFile);
const writeFile = util.promisify(fs.writeFile);
const readPackage = async () => {
  const packageJson = await readFile(path.join(sourcePath, versionFile), {
    encoding: "utf8"
  });
  return JSON.parse(packageJson);
};

readPackage().then(async content => {
  if (versionAutorelease === "COMPUTE") {
    let dNow = new Date()
      .toISOString()
      .replace(/[^0-9]/g, "")
      .substr(0, 14);
    versionAutorelease = `${content.version}-dev${dNow}`;
  }
  console.log("BUMP VERSION");
  content.version = versionAutorelease;
  await writeFile(path.join(sourcePath, versionFile), JSON.stringify(content));
});
