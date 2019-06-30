const gulp = require("gulp");
const tar = require("gulp-tar");
const gzip = require("gulp-gzip");
const streamqueue = require("streamqueue");
const replace = require("gulp-replace");
const { getModuleInfo } = require("../utils/moduleInfo");
const path = require("path");
const fs = require("fs");
const appConst = require("../utils/appConst");
const { Signale } = require("signale");

const interactiveLog = new Signale({ scope: "build" });

const buildPipe = (exports.buildPipe = async ({
  sourcePath,
  autoRelease = false,
  localName = false
}) => {
  const moduleInfo = await getModuleInfo(sourcePath);
  let version = moduleInfo.moduleInfo.version;
  let release = "";

  if (autoRelease === "") {
    autoRelease = "dev";
  }
  if (autoRelease) {
    let dNow = new Date()
      .toISOString()
      .replace(/[^0-9]/g, "")
      .substr(0, 14);
    version =
      moduleInfo.moduleInfo.version + `-${autoRelease}${release}${dNow}`;
  }
  let moduleFileName = `${moduleInfo.moduleInfo.name}-${version}`;
  if (localName) {
    moduleFileName = localName;
  }
  const mainFiles = gulp
    .src(path.join(moduleInfo.buildInfo.buildPath[0], "**"), {
      dot: true
    })
    .pipe(tar("content"))
    .pipe(gzip({ extension: "tar.gz" }));
  let infoXML = gulp.src(path.join(sourcePath, appConst.infoPath));

  const originalVersion = moduleInfo.moduleInfo.version;
  if (autoRelease) {
    let autoDone = false;
    // Only replace the first version attribute - Not the best algo
    infoXML = infoXML.pipe(
      replace(`version="${originalVersion}"`, match => {
        if (autoDone === false) {
          autoDone = true;
          return `version="${version}"`;
        } else {
          return match;
        }
      })
    );
  }

  let gulpElements = streamqueue({ objectMode: true }, mainFiles, infoXML);
  if (fs.existsSync(path.join(sourcePath, appConst.license))) {
    gulpElements = streamqueue(
      { objectMode: true },
      gulpElements,
      gulp.src(path.join(sourcePath, appConst.license))
    );
  }

  interactiveLog.info(`Generate  ${moduleInfo.moduleInfo.name}-${version}.app`);
  interactiveLog.info("Version " + version);
  return gulpElements
    .pipe(tar(moduleFileName))
    .pipe(gzip({ extension: "app" }));
});

exports.build = ({
  sourcePath = ".",
  targetPath = ".",
  autoRelease = false
}) => {
  return gulp.task("build", async () => {
    // eslint-disable-next-line no-async-promise-executor
    return new Promise(async (resolve, reject) => {
      try {
        const build = await buildPipe({ sourcePath, autoRelease });
        build
          .pipe(gulp.dest(targetPath))
          .on("end", resolve)
          .on("error", reject);
      } catch (e) {
        reject(e);
      }
    });
  });
};
