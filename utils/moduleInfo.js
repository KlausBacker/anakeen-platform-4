const fs = require("fs");
const path = require("path");
const appConst = require("./appConst");
const xml2js = require("xml2js");
const glob = require("glob");
const Saxophone = require("saxophone");
const signale = require("signale");

const REG_EXP_CHECK_NAMESPACE = /https:\/\/platform.anakeen.com\/4\/schemas\/smart\/1.0/;
const REG_EXP_STRUCTURE_CONF = /[\w]?:?structure-configuration/;
const REG_EXP_CLASS_CONF = /[\w]?:?class/;

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
              result.release = data.module.$.release;
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
                path.join(sourcePath, data.config.sources[0].source[0].$.path)
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

exports.getStructureFiles = async ({ buildPath }) => {
  return Promise.all(
    buildPath.map(currentBuildPath => {
      return new Promise((resolve, reject) => {
        glob(currentBuildPath + "/**/*.xml", (err, paths) => {
          if (err) {
            reject(err);
            return;
          }
          resolve(paths);
        });
      });
    })
  )
    .then(paths => {
      return [].concat(...paths);
    })
    .then(paths => {
      return Promise.all(
        paths.map(currentPath => {
          return new Promise((resolve, reject) => {
            const structure = {};
            let currentStructName;
            let inClassMode = false;
            let keepIt = false;
            const parser = new Saxophone();
            parser.on("tagopen", tagopen => {
              inClassMode = false;
              if (REG_EXP_STRUCTURE_CONF.test(tagopen.name)) {
                const attributes = Saxophone.parseAttrs(tagopen.attrs);
                if (attributes.name) {
                  const currentValues = structure[attributes.name]
                    ? structure[attributes.name]
                    : {};
                  structure[attributes.name] = {
                    ...currentValues,
                    ...attributes
                  };
                  currentStructName = attributes.name;
                }
              }
              if (REG_EXP_CLASS_CONF.test(tagopen.name)) {
                inClassMode = true;
              }
              if (
                !keepIt &&
                tagopen.attrs &&
                REG_EXP_CHECK_NAMESPACE.test(tagopen.attrs)
              ) {
                keepIt = true;
              }
            });
            parser.on("tagclose", tagclose => {
              if (REG_EXP_CLASS_CONF.test(tagclose.name)) {
                inClassMode = false;
              }
            });
            parser.on("error", err => {
              signale.error(currentPath, err);
              keepIt = false;
            });
            parser.on("text", content => {
              if (inClassMode && content.contents) {
                structure[currentStructName]["class"] = content.contents;
              }
            });
            parser.on("finish", () => {
              if (keepIt) {
                resolve({ path: currentPath, structure });
              } else {
                resolve(false);
              }
            });
            fs.readFile(currentPath, (err, buffer) => {
              if (err) {
                reject(err);
                return;
              }
              parser.parse(buffer);
            });
          });
        })
      );
    })
    .then(paths => {
      return paths.filter(currentPath => {
        return currentPath;
      });
    });
};
