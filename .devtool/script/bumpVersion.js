const path = require("path");
const fs = require("fs");
const util = require("util");
const child_process = require("child_process");
const { bumpVersion } = require("./util/bump-version");
const libxml = require("libxmljs");
const versions = require(path.resolve(__dirname, "./versions.js"));

const info = "info.xml";
const readFile = util.promisify(fs.readFile);
const writeFile = util.promisify(fs.writeFile);
const exec = util.promisify(child_process.exec);

const tag = process.argv[2];

const bumpNpmVersion = async ({ package, version, dir = "." }) => {
  return await bumpVersion(package, version, dir);
};

const bumpDepsInfoXML = async ({ moduleName, version }) => {
  return versions.modulePath.reduce((acc, currentPath) => {
    return acc.then(async () => {
      const infoPath = path.resolve(currentPath, info);
      const xmlFile = await readFile(infoPath, { encoding: "utf8" });
      const document = libxml.parseXmlString(xmlFile);
      const requires = document
        .root()
        .get("module:requires", { module: "https://platform.anakeen.com/4/schemas/app/1.0" });
      return requires.childNodes().reduce((acc, currentNode) => {
        return acc.then(async () => {
          if (currentNode.name() === "module" && currentNode.attr("name").value() === moduleName) {
            currentNode.attr("version").value(`^${version}`);
            return await writeFile(infoPath, document.toString());
          }
        });
      }, Promise.resolve());
    });
  }, Promise.resolve());
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
          await bumpDepsInfoXML(currentVersion);
        }
      } else {
        await tagVersion(currentVersion);
      }
    });
  }, Promise.resolve())
  .then(async () => {
    if (tag) {
      console.log("Add general tag");
      return await exec(`git tag -f v${versions.release}${versions.RC ? "." + versions.RC : ""}`);
    }
    return Promise.resolve();
  })
  .then(() => {
    console.log("Done");
  });
