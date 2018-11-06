const gulp = require("gulp");
const {
  xmlStructure2Pot,
  xmlEnum2Pot,
  php2Pot,
  js2Po,
  msgmergeStructure,
  msgmergeMustache,
  msgmergeEnum
} = require("../utils/POExtractor");
const { getModuleInfo, getStructureFiles } = require("../utils/moduleInfo");
const asyncCallback = require("./plugins/asyncCallback");
const mustache2Pot = require("./plugins/POExtractorMustache");
const { Signale } = require("signale");
const signale = require("signale");
const path = require("path");
const fs = require("fs");

const TMPPO = "tmppot";

const deleteFolderRecursive = path => {
  if (fs.existsSync(path)) {
    fs.readdirSync(path).forEach(file => {
      const curPath = path + "/" + file;
      if (fs.lstatSync(curPath).isDirectory()) {
        // recurse
        deleteFolderRecursive(curPath);
      } else {
        // delete file
        fs.unlinkSync(curPath);
      }
    });
    fs.rmdirSync(path);
  }
};

exports.po = ({ sourcePath }) => {
  const potPath = path.join(sourcePath, TMPPO);

  gulp.task("poMustache", async (resolveEnum, rejectEnum) => {
    const tmpMuPot = potPath + "must.pot";

    return new Promise(async (resolve, reject) => {
      if (sourcePath === undefined) {
        signale.error("No source path specified.");
        return;
      }
      try {
        const info = await getModuleInfo(sourcePath);
        const buildPath = info.buildInfo.buildPath[0];

        // mustache file
        gulp
          .src(buildPath + "/**/*.mustache")
          .pipe(mustache2Pot(tmpMuPot, info))
          .pipe(gulp.dest(potPath))
          .pipe(
            asyncCallback(file => {
              return msgmergeMustache(file, info);
            }, true)
          )
          .pipe(
            gulp.dest(file => {
              return `${buildPath}/locale/${file.lang}/LC_MESSAGES/src/`;
            })
          )
          .on("end", resolve)
          .on("error", reject);
      } catch (e) {
        reject(e);
      }
    }).then(resolveEnum, rejectEnum);
  });

  gulp.task("poJs", async (resolveJs, rejectJs) => {
    const info = await getModuleInfo(sourcePath);
    const poConfig = info.buildInfo.build.config["po-config"];
    let poJs = null;

    if (poConfig) {
      poJs = poConfig[0]["po-js"];
    }
    if (!poJs) {
      return new Promise(resolve => {
        resolve();
      }).then(resolveJs);
    }
    let resolvCount = 0;

    return new Promise(resolve => {
      poJs.forEach(jsItem => {
        js2Po(jsItem.$.source, jsItem.$.target, info, potPath).then(() => {
          resolvCount++;
          if (resolvCount >= poJs.length) {
            resolve();
          }
        }, rejectJs);
      });
    }).then(resolveJs, rejectJs);
  });
  gulp.task("poPhp", async (resolvePhp, rejectPhp) => {
    const info = await getModuleInfo(sourcePath);

    return php2Pot(info, potPath).then(resolvePhp, rejectPhp);
  });
  gulp.task("poEnum", async (resolveEnum, rejectEnum) => {
    return new Promise(async (resolve, reject) => {
      if (sourcePath === undefined) {
        signale.error("No source path specified.");
        return;
      }
      try {
        const info = await getModuleInfo(sourcePath);
        const buildPath = info.buildInfo.buildPath;
        const structureFiles = await getStructureFiles({ buildPath });
        const files = structureFiles.map(currentStruct => {
          return currentStruct.path;
        });

        // Smart structure
        gulp
          .src(files)
          .pipe(asyncCallback(xmlEnum2Pot, true))
          .pipe(gulp.dest(potPath))
          .pipe(
            asyncCallback(file => {
              return msgmergeEnum(file, buildPath);
            }, true)
          )
          .pipe(
            gulp.dest(file => {
              return `${buildPath}/locale/${file.lang}/LC_MESSAGES/src/`;
            })
          )
          .on("end", () => {
            //php2Pot(info, potPath).then(resolve);
            resolve();
          })
          .on("error", reject);
      } catch (e) {
        reject(e);
      }
    }).then(resolveEnum, rejectEnum);
  });
  gulp.task("poSmart", async () => {
    return new Promise(async (resolve, reject) => {
      if (sourcePath === undefined) {
        signale.error("No source path specified.");
        return;
      }
      const interactive = new Signale({ scope: "po" });
      const log = message => {
        interactive.info(message);
      };
      try {
        log("Analyze package");
        const info = await getModuleInfo(sourcePath);
        const buildPath = info.buildInfo.buildPath;
        const structureFiles = await getStructureFiles({ buildPath });
        const files = structureFiles.map(currentStruct => {
          return currentStruct.path;
        });

        // Smart structure
        if (files.length === 0) {
          return resolve();
        }
        log("Extract smart structure");

        gulp
          .src(files)
          .pipe(asyncCallback(xmlStructure2Pot, true))
          .pipe(gulp.dest(potPath))
          .pipe(
            asyncCallback(file => {
              return msgmergeStructure(file, buildPath);
            }, true)
          )
          .pipe(
            gulp.dest(file => {
              return `${buildPath}/locale/${file.lang}/LC_MESSAGES/src/`;
            })
          )
          .on("end", () => {
            log("Extract enum");
            gulp.task("poEnum")(() => {
              log("Extract mustache");
              gulp.task("poMustache")(() => {
                log("Extract php");
                gulp.task("poPhp")(() => {
                  log("Extract JS");
                  gulp.task("poJs")(() => {
                    //Delete temp repo
                    log("Suppress temp directory");
                    deleteFolderRecursive(potPath);
                    resolve();
                  }, reject);
                }, reject);
              }, reject);
            }, reject);
          })
          .on("error", reject);
      } catch (e) {
        reject(e);
      }
    });
  });
};
