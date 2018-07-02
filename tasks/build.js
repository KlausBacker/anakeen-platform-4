const gulp = require("gulp");
const tar = require("gulp-tar");
const gzip = require("gulp-gzip");
const streamqueue = require("streamqueue");
const replace = require("gulp-replace");
const { getModuleInfo } = require("../utils/moduleInfo");
const path = require("path");
const appConst = require("../utils/appConst");

const buildPipe = (exports.buildPipe = async ({
  sourcePath,
  autoRelease = false
}) => {
  const moduleInfo = await getModuleInfo(sourcePath);
  let release = moduleInfo.moduleInfo.release;
  if (autoRelease) {
    release += Date.now();
  }
  const moduleFileName = `${moduleInfo.moduleInfo.name}-${
    moduleInfo.moduleInfo.version
  }-${release}`;
  const buildPath = moduleInfo.buildInfo.config.sources[0].source.map(
    currentSource => {
      return path.join(sourcePath, currentSource.$.path, "**");
    }
  );
  const mainFiles = gulp
    .src(buildPath)
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
  return streamqueue({ objectMode: true }, mainFiles, infoXML)
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
