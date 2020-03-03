#!/usr/bin/env node

const repoContent = require("../../anakeen-cli/utils/RepoContentXML");
const path = require("path");
const glob = require("glob");

const BUILD_PATH = path.resolve(__dirname, "../../build/");

const main = async () => {
  const repo = new repoContent.RepoContentXML(
    path.join(BUILD_PATH, "content.xml")
  );
  repo.data = repo.repoContentXMLTemplate();
  return new Promise((resolve, reject) => {
    glob(
      "**/*.app",
      {
        cwd: path.resolve(BUILD_PATH),
        nodir: true
      },
      (err, files) => {
        if (err) {
          return reject(err);
        }
        resolve(files);
      }
    );
  })
    .then(files => {
      return files.map(currentFile => {
        return path.join(BUILD_PATH, currentFile);
      });
    })
    .then(files => {
      return Promise.all(
        files.map(async currentFile => {
          return await repo.addModuleFile(currentFile);
        })
      );
    })
    .then(files => {
      return repo.save();
    });
};

main()
  .then(() => {
    console.log("OK");
  })
  .catch(e => {
    console.error(e);
    process.exit(1);
  });
