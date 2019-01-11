const gulp = require("gulp");
const tar = require("gulp-tar");
const gzip = require("gulp-gzip");
const streamqueue = require("streamqueue");
const replace = require("gulp-replace");
const { getModuleInfo } = require("../utils/moduleInfo");
const path = require("path");
const fs = require("fs");
const appConst = require("../utils/appConst");

const buildPipe = (exports.buildPipe = async ({
  sourcePath,
  autoRelease = false,
  localName = false
}) => {
  const moduleInfo = await getModuleInfo(sourcePath);
  let release = moduleInfo.moduleInfo.release;
  if (autoRelease) {
    let dNow = new Date()
      .toISOString()
      .replace(/[^0-9]/g, "")
      .substr(0, 14);
    release = `dev${release}${dNow}`;
  }
  let moduleFileName = `${moduleInfo.moduleInfo.name}-${
    moduleInfo.moduleInfo.version
  }-${release}`;
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
  if (autoRelease) {
    infoXML = infoXML.pipe(
      replace(
        `release="${moduleInfo.moduleInfo.release}"`,
        `release="${release}"`
      )
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
