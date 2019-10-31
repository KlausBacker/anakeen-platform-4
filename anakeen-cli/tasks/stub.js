const gulp = require("gulp");
const { parseStub, parseWorkflowContants, parseEnumContants } = require("../utils/STUBGenerator");
const { getModuleInfo } = require("../utils/moduleInfo");
const { Signale } = require("signale");
const { analyzeXML } = require("../utils/globAnalyze");

exports.stub = ({ sourcePath, targetPath = "./stubs", verbose }) => {
  gulp.task("stubFields", async () => {
    if (sourcePath === undefined) {
      throw new Error("No source path specified.");
    }
    try {
      const interactive = new Signale({ scope: "stub struct" });
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
        return await Promise.resolve();
      }

      return parseStub({ globFile, info, targetPath, log, verbose });
    } catch (e) {
      return await Promise.reject(e);
    }
  });

  gulp.task("stubWorflows", async () => {
    if (sourcePath === undefined) {
      throw new Error("No source path specified.");
    }
    try {
      const interactive = new Signale({ scope: "stub workflow" });
      const log = (message, type = "info") => {
        if (type === "warning") {
          interactive.warn(message);
        } else {
          interactive.info(message);
        }
      };
      const info = await getModuleInfo(sourcePath);
      const stub = info.buildInfo.build.config["stub-config"];
      let globXML = [];

      if (stub) {
        globXML = stub[0]["stub-workflow"];
      }

      if (!globXML) {
        log("No workflow stub configuration set in build.xml");
        return await Promise.resolve();
      }
      const globFile = analyzeXML(globXML);

      if (!globFile) {
        log("No workflow to extract");
        return await Promise.resolve();
      }

      return parseWorkflowContants({ globFile, info, targetPath, log, verbose });
    } catch (e) {
      return await Promise.reject(e);
    }
  });
  gulp.task("stubEnums", async () => {
    if (sourcePath === undefined) {
      throw new Error("No source path specified.");
    }
    try {
      const interactive = new Signale({ scope: "stub enum" });

      const log = (message, type = "info") => {
        if (type === "warning") {
          interactive.warn(message);
        } else {
          interactive.info(message);
        }
      };
      const info = await getModuleInfo(sourcePath);
      const stub = info.buildInfo.build.config["stub-config"];
      let globXML = [];

      if (stub) {
        globXML = stub[0]["stub-enumerate"];
      }

      if (!globXML) {
        log("No enum stub configuration set in build.xml");
        return await Promise.resolve();
      }
      const globFile = analyzeXML(globXML);

      if (!globFile) {
        log("No enum to extract");
        return await Promise.resolve();
      }

      return parseEnumContants({ globFile, info, targetPath, log, verbose });
    } catch (e) {
      return await Promise.reject(e);
    }
  });

  return gulp.task("allStubs", async () => {
    // eslint-disable-next-line no-async-promise-executor
    return new Promise(async (resolve, reject) => {
      try {
        await gulp.task("stubFields")();
        await gulp.task("stubWorflows")();
        await gulp.task("stubEnums")();

        resolve();
      } catch (e) {
        reject(e);
      }
    });
  });
};
