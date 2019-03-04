#!/usr/bin/env node

const fs = require("fs");
const util = require("util");
const { produceApp } = require("@anakeen/anakeen-ci");
const { getModuleInfo } = require("@anakeen/anakeen-cli/utils/moduleInfo");

const readFile = util.promisify(fs.readFile);
const readPackage = async () => {
  const packageJson = await readFile("./package.json", { encoding: "utf8" });
  return JSON.parse(packageJson);
};

readPackage()
  .then(async content => {
    await produceApp({
      apps: [
        {
          app: {
            command: "make",
            args: [content.autorelease ? "app-autorelease" : "app"]
          },
          path: {
            infoXML: "./",
            src: "./src/"
          },
          src: true
        }
      ],
      getModuleInfo
    });
  })
  .then(() => {
    return produceApp({
      apps: [
        {
          app: {
            command: "make",
            args: [content.autorelease ? "app-test-autorelease" : "app-test"]
          },
          path: {
            infoXML: "./Tests",
            src: "./Tests/src/"
          },
          src: true
        }
      ],
      getModuleInfo
    });
  })
  .then(() => {
    console.log("OK");
  })
  .catch(err => {
    console.error(err);
    process.exit(42);
  });
