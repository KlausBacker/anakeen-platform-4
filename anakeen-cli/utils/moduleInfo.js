const fs = require("fs");
const path = require("path");
const appConst = require("./appConst");
const xml2js = require("xml2js");
const { checkFile } = require("@anakeen/anakeen-module-validation");

exports.getModuleInfo = async sourcePath => {
  let existsSourcePath = fs.existsSync(sourcePath);
  let existsInfoPath = fs.existsSync(path.join(sourcePath, appConst.infoPath));
  let existsBuildPath = fs.existsSync(
    path.join(sourcePath, appConst.buildPath)
  );

  if (!existsSourcePath) {
    throw new Error(`Unable to find the source "${sourcePath}"`);
  }
  if (!existsInfoPath) {
    throw new Error(
      `Unable to find the source info "${path.join(
        sourcePath,
        appConst.infoPath
      )}"`
    );
  }
  if (!existsBuildPath) {
    throw new Error(
      `Unable to find the source build "${path.join(
        sourcePath,
        appConst.buildPath
      )}"`
    );
  }

  const checkBuild = checkFile(path.join(sourcePath, appConst.buildPath));

  if (checkBuild.error) {
    return Promise.reject(checkBuild.error);
  }

  const checkInfo = checkFile(path.join(sourcePath, appConst.infoPath));

  if (checkInfo.error) {
    return Promise.reject(checkInfo.error);
  }

  return Promise.all([
    new Promise((resolve, reject) => {
      fs.readFile(
        path.join(sourcePath, appConst.infoPath),
        { encoding: "utf8" },
        (err, content) => {
          if (err) reject(err);
          xml2js.parseString(
            content,
            { tagNameProcessors: [xml2js.processors.stripPrefix] },
            (err, data) => {
              if (err) reject(err);
              const result = {};
              result.vendor = data.module.$.vendor;
              result.name = data.module.$.name;
              result.version = data.module.$.version;
              resolve(result);
            }
          );
        }
      );
    }),
    new Promise((resolve, reject) => {
      fs.readFile(
        path.join(sourcePath, appConst.buildPath),
        { encoding: "utf8" },
        (err, content) => {
          if (err) return reject(err);
          xml2js.parseString(
            content,
            { tagNameProcessors: [xml2js.processors.stripPrefix] },
            (err, data) => {
              if (err) return reject(err);
              const buildPath = [
                path.join(sourcePath, data.config.source[0].$.path)
              ];
              resolve({ build: data, buildPath });
            }
          );
        }
      );
    })
  ]).then(results => {
    return {
      sourcePath: sourcePath,
      moduleInfo: results[0],
      buildInfo: results[1]
    };
  });
};
