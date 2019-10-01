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

const bumpNpmVersion = async ({ package, version, dir = "." }) => {
  return await bumpVersion(package, version, dir).catch(() => {
    console.error("ERRROOOORRRR", package, version, dir);
  });
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
  return await exec(`git tag "${package}-${version}"`);
};

console.log(versions);

const release = `${versions.RC ? "RC." : ""}${versions.release}${versions.RC ? "." + versions.RC : ""}`;

console.log(`Release is : ${release}`);

Promise.all(
  versions.versions.map(async currentVersion => {
    if (!tag) {
      if (versions.RC) {
        currentVersion.version = `${currentVersion.version}-${release}`;
      }
      console.log(`Handle ${JSON.stringify(currentVersion)}`);
      await bumpNpmVersion(currentVersion);
      if (!currentVersion.npmOnly) {
        await bumpInfoXML(currentVersion).catch(err => {
          console.error("ERROR", JSON.stringify(currentVersion), err);
        });
      }
    } else {
      await tagVersion(currentVersion);
    }
  })
)
  .then(async () => {
    if (tag) {
      console.log("Add general tag");
      return await exec(`git tag ${versions.release}${versions.RC ? "." + versions.RC : ""}`);
    }
    return Promise.resolve();
  })
  .then(() => {
    console.log("Done");
  });
