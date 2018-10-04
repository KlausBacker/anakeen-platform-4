const gulp = require("gulp");
const {
  xmlStructure2Pot,
  xmlEnum2Pot,
  php2Pot,
  msgmergeStructure,
  msgmergeMustache,
  msgmergeEnum
} = require("../utils/POExtractor");
const { getModuleInfo, getStructureFiles } = require("../utils/moduleInfo");
const asyncCallback = require("./plugins/asyncCallback");
const mustache2Pot = require("./plugins/POExtractorMustache");
const signale = require("signale");

exports.po = ({ sourcePath }) => {
  const potPath = sourcePath + "/tmppot/";

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

        // Smart structure
        gulp
          // .src(buildPath + "/**/*.mustache")
          .src(buildPath + "/**/*.mustache")
          .pipe(mustache2Pot(tmpMuPot, info))
          /*.pipe(
            asyncCallback(file => {
              return mustache2Pot(file, info);
            }, true)
          )*/

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
            php2Pot(info, potPath).then(resolve);
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
            gulp.task("poEnum")(() => {
              gulp.task("poMustache")(resolve, reject);
            }, reject);
          })
          .on("error", reject);
      } catch (e) {
        reject(e);
      }
    });
  });
};
