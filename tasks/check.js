const gulp = require("gulp");
const { getModuleInfo } = require("../utils/moduleInfo");
const { checkGlobElements } = require("../utils/check");
const { Signale } = require("signale");

exports.check = ({ sourcePath }) => {
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
      const stub = info.buildInfo.build.config["stub-config"];
      let globXML = [];

      if (stub) {
        globXML = stub[0]["stub-struct"];
      }

      const globFile = globXML.map(currentElement => {
        return currentElement.$.source;
      });

      if (!globFile) {
        log("No glob xml to check");
        return Promise.resolve();
      }

      return checkGlobElements({ globFile, srcPath: info.sourcePath });
    } catch (e) {
      return Promise.reject(e);
    }
  });
};
