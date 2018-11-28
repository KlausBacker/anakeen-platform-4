const gulp = require("gulp");
const { getModuleInfo } = require("../utils/moduleInfo");
const { checkGlobElements } = require("../utils/check");
const { analyzeXML } = require("../utils/globAnalyze");
const { Signale } = require("signale");

exports.check = ({ sourcePath, verbose }) => {
  return gulp.task("check", async () => {
    if (sourcePath === undefined) {
      throw new Error("No source path specified.");
    }
    try {
      const interactive = new Signale({ scope: "check" });
      const log = message => {
        interactive.info(message);
      };
      const info = await getModuleInfo(sourcePath);
      const stub = info.buildInfo.build.config["check-config"];
      let globXML = [];

      if (stub) {
        globXML = stub[0]["config-xml"];
      }

      const globFile = analyzeXML(globXML);

      if (globFile.addGlob === 0) {
        log("No glob xml to check");
        return Promise.resolve();
      }

      return checkGlobElements({
        globFile,
        srcPath: info.sourcePath,
        verbose,
        log
      });
    } catch (e) {
      return Promise.reject(e);
    }
  });
};
