#!/usr/bin/env node

const fs = require("fs");
const path = require("path");
const util = require("util");
const exec = util.promisify(require("child_process").exec);
const writeFile = util.promisify(fs.writeFile);
const copyFile = util.promisify(fs.copyFile);
const { getModuleInfo } = require("@anakeen/anakeen-cli/utils/moduleInfo");

const getFileName = moduleInfo => {
  return `${moduleInfo.name}-${moduleInfo.version}-${moduleInfo.release}`;
};

const produceAndUpload = async (srcPath = "./", testPath = "./Tests") => {
  try {
    const outputPath = process.env.CIBUILD_OUTPUTS;
    const { moduleInfo } = await getModuleInfo(srcPath);
    const moduleTest = await getModuleInfo(testPath);
    console.log("Make app");
    await exec("make app");
    console.log("Make app-test");
    await exec("make app-test");
    console.log("Make stubs");
    await exec("make stub");
    console.log("tar src and stubs");
    await exec(
      `tar -czf ${moduleInfo.name}-${moduleInfo.version}-${
        moduleInfo.release
        }.src ./src ./stubs`
    );
    console.log("Make app json");
    const modules = [
      {
        name: moduleInfo.name,
        version: moduleInfo.version,
        resources: {
          app: getFileName(moduleInfo) + ".app",
          src: getFileName(moduleInfo) + ".src"
        },
        data: {
          date: new Date().toISOString(),
          commit: {
            sha: process.env.CI_COMMIT_SHA,
            title: process.env.CI_COMMIT_TITLE
          }
        }
      },
      {
        name: moduleTest.moduleInfo.name,
        version: moduleTest.moduleInfo.version,
        resources: {
          app: getFileName(moduleTest.moduleInfo) + ".app"
        },
        data: {
          date: new Date().toISOString(),
          commit: {
            sha: process.env.CI_COMMIT_SHA,
            title: process.env.CI_COMMIT_TITLE
          }
        }
      }
    ];
    await writeFile(path.join(outputPath, "app.json"), JSON.stringify(modules));
    console.log("Move files");
    await copyFile(
      getFileName(moduleInfo) + ".app",
      path.join(outputPath, getFileName(moduleInfo) + ".app")
    );
    await copyFile(
      getFileName(moduleInfo) + ".src",
      path.join(outputPath, getFileName(moduleInfo) + ".src")
    );
    await copyFile(
      getFileName(moduleTest.moduleInfo) + ".app",
      path.join(outputPath, getFileName(moduleTest.moduleInfo) + ".app")
    );
  } catch (error) {
    console.error(error);
    throw new Error(error);
  }
  return true;
};

return produceAndUpload()
  .then(() => {
    console.log("OK");
  })
  .catch(err => {
    throw new Error(err);
  });
