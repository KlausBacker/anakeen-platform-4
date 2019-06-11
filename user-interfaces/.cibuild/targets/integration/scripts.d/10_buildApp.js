#!/usr/bin/env node

const fs = require("fs");
const path = require("path");
const util = require("util");
const { produceApp } = require("@anakeen/anakeen-ci");
const { getModuleInfo } = require("@anakeen/anakeen-cli/utils/moduleInfo");

const writeFile = util.promisify(fs.writeFile);
const readFile = util.promisify(fs.readFile);

const outputPath = process.env.CIBUILD_OUTPUTS;
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
    return content;
  })
  .then(async (content) => {
    const result = JSON.parse(await readFile(path.join(outputPath, "app.json"), "utf8"));
    await produceApp({
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
    const resultTest = JSON.parse(await readFile(path.join(outputPath, "app.json"), "utf8"));
    return await writeFile(
      path.join(outputPath, "app.json"),
      JSON.stringify([...result, ...resultTest])
    );
  })
  .then(() => {
    console.log("OK");
  })
  .catch(err => {
    console.error(err);
    process.exit(42);
  });
