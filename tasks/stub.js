const gulp = require("gulp");
const { parseStub } = require("../utils/STUBGenerator");
const { getModuleInfo } = require("../utils/moduleInfo");
const { Signale } = require("signale");
const { analyzeXML } = require("../utils/globAnalyze");

exports.stub = ({ sourcePath, targetPath = "./stubs", verbose }) => {
  return gulp.task("stub", async () => {
    if (sourcePath === undefined) {
      throw new Error("No source path specified.");
    }
    try {
      const interactive = new Signale({ scope: "stub" });
      const log = message => {
        interactive.info(message);
      };
      const info = await getModuleInfo(sourcePath);
      const stub = info.buildInfo.build.config["stub-config"];
      let globXML = [];

      if (stub) {
        globXML = stub[0]["stub-struct"];
      }

      const globFile = analyzeXML(globXML);

      if (!globFile) {
        log("No smart element to extract");
        return Promise.resolve();
      }

      return parseStub({ globFile, info, targetPath, log, verbose });
    } catch (e) {
      return Promise.reject(e);
    }
  });
};
