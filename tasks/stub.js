const gulp = require("gulp");
const { getSTUBgenerator } = require("../utils/STUBGenerator");
const path = require("path");
const signale = require("signale");

exports.stub = ({ sourcePath, targetPath = "./stubs" }) => {
  return gulp.task("stub", async () => {
    if (sourcePath === undefined) {
      signale.error("No source path specified.");
      return;
    }
    try {
      if (targetPath.startsWith("./")) {
        targetPath = sourcePath + targetPath.substr(1);
      }
      const STUBGenerator = await getSTUBgenerator(sourcePath, targetPath);
      gulp
        .src(path.join(STUBGenerator.extractDir, "**"))
        .pipe(gulp.dest(targetPath));
    } catch (e) {
      throw e;
    }
  });
};
