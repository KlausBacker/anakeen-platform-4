const path = require("path");
const fs = require("fs");
const util = require("util");
const child_process = require("child_process");
const { bumpVersion } = require("yarn-version-bump/src/bump-version");
const libxml = require("libxmljs");
const versions = require(path.resolve(__dirname, "./versions.js"));

const info = "info.xml";
const readFile = util.promisify(fs.readFile);
const writeFile = util.promisify(fs.writeFile);
const exec = util.promisify(child_process.exec);

const tag = process.argv[2];

console.log("tag", tag);

const bumpNpmVersion = async ({ package, version, dir = "." }) => {
  console.log(package, version, dir);
  return await bumpVersion(package, version, dir);
};

const bumpInfoXML = async ({ modulePath, version }) => {
  const infoPath = path.resolve(modulePath, info);
  const xmlFile = await readFile(infoPath, { encoding: "utf8" });
  const document = libxml.parseXmlString(xmlFile);
  const module = document.root();
  module.attr("version", version);
  const newInfo = document.toString();
  return await writeFile(infoPath, newInfo);
};

const tagVersion = async ({ package, version }) => {
  return await exec(`git tag -f "${package}-${version}"`);
};

const release = `${versions.RC ? "RC." : ""}${versions.release}${versions.RC ? "." + versions.RC : ""}`;

console.log(`Release is : ${release}`);

versions.versions
  .reduce((acc, currentVersion) => {
    return acc.then(async () => {
      if (versions.RC) {
        currentVersion.version = `${currentVersion.version}-${release}`;
      }
      if (!tag) {
        console.log(`Handle ${JSON.stringify(currentVersion)}`);
        await bumpNpmVersion(currentVersion);
        if (!currentVersion.npmOnly) {
          await bumpInfoXML(currentVersion);
        }
      } else {
        await tagVersion(currentVersion);
      }
    });
  }, Promise.resolve())
  .then(async () => {
    if (tag) {
      console.log("Add general tag");
      return await exec(`git tag -f ${versions.release}${versions.RC ? "." + versions.RC : ""}`);
    }
    return Promise.resolve();
  })
  .then(() => {
    console.log("Done");
  });
