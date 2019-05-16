const gulp = require("gulp");
const { getModuleInfo } = require("../utils/moduleInfo");
const { checkGlobElements } = require("../utils/check");
const { analyzeXML } = require("../utils/globAnalyze");
const { checkFile } = require("@anakeen/anakeen-module-validation");
const { Signale } = require("signale");
const globFunction = require("glob");
const path = require("path");

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

exports.checkConfigFile = ({ fileToCheck, verbose, glob, sourceDir }) => {
  return gulp.task(
    "checkConfigFile",
    async () =>
      new Promise((resolve, reject) => {
        let result = "";
        if (fileToCheck === undefined && glob === undefined) {
          throw new Error("No source path specified.");
        }
        try {
          const interactive = new Signale({ scope: "checkConfigFile" });
          const log = message => {
            interactive.info(message);
          };
          if (fileToCheck) {
            const checkResult = checkFile(fileToCheck);
            if (verbose) {
              result = checkResult.ok
                ? "✓"
                : checkResult.ignore
                ? "ignored"
                : checkResult.error;
              log(`Analyze : ${fileToCheck} : ${result}`);
            }
            if (checkResult.error) {
              result += checkResult.error;
            }
            resolve(result);
          } else if (glob) {
            const globOpts = {
              nodir: true,
              cwd: sourceDir || process.cwd()
            };
            globFunction(glob, globOpts, (err, files) => {
              if (err) {
                reject(err);
              } else {
                files.forEach(file => {
                  const filepath = path.resolve(globOpts.cwd, file);
                  const checkResult = checkFile(filepath);
                  if (verbose) {
                    result = checkResult.ok
                      ? "✓"
                      : checkResult.ignore
                      ? "ignored"
                      : checkResult.error;
                    log(`Analyze : ${filepath} : ${result}`);
                  }
                });
                resolve(result);
              }
            });
          }
        } catch (e) {
          reject(e);
        }
      })
  );
};
