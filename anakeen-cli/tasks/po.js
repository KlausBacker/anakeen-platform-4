const gulp = require("gulp");
const {
  xmlStructure2Pot,
  xmlEnum2Pot,
  xmlCVDOC2Pot,
  xmlWorkflow2Pot,
  php2Po,
  js2Po,
  msgmerge,
  msgmergeMustache
} = require("../utils/POExtractor");
const { getModuleInfo } = require("../utils/moduleInfo");
const { analyzeXML } = require("../utils/globAnalyze");
const mustache2Pot = require("../utils/POExtractorMustache");
const { Signale } = require("signale");
const signale = require("signale");
const path = require("path");
const fs = require("fs");

const TMPPO = "tmppot";

/**
 * Delete a folder and all the files inside
 * @param path
 */
const deleteFolderRecursive = path => {
  if (fs.existsSync(path)) {
    fs.readdirSync(path).forEach(file => {
      const curPath = path + "/" + file;
      if (fs.lstatSync(curPath).isDirectory()) {
        // recurse
        deleteFolderRecursive(curPath);
      } else {
        // delete file
        fs.unlinkSync(curPath);
      }
    });
    fs.rmdirSync(path);
  }
};

exports.po = ({ sourcePath, verbose }) => {
  const potPath = path.join(sourcePath, TMPPO);
  const interactive = new Signale({ scope: "po" });
  const log = message => {
    interactive.info(message);
  };

  /**
   * Extract the mustache part
   */
  gulp.task("poMustache", async () => {
    const info = await getModuleInfo(sourcePath);
    const srcPath = info.buildInfo.buildPath[0];
    const poConfig = info.buildInfo.build.config["po-config"];
    let poEntry = null;

    if (poConfig) {
      poEntry = poConfig[0]["po-mustache"];
    }
    if (!poEntry || poEntry.length === 0) {
      log("No mustache template to extract");
      return Promise.resolve();
    }

    //Order glob by target
    const globByTargets = poEntry.reduce((acc, currentElement) => {
      if (!acc[currentElement.$.target]) {
        acc[currentElement.$.target] = [];
      }
      acc[currentElement.$.target].push(currentElement);
      return acc;
    }, {});

    log("Extract Mustache template");
    return Promise.all(
      Object.keys(globByTargets).map(currentKey => {
        return mustache2Pot({
          globFile: analyzeXML(globByTargets[currentKey]),
          targetName: currentKey,
          info,
          potPath,
          verbose,
          log
        });
      })
    ).then(files => {
      //Flat files element
      files = files.reduce((acc, currentFiles) => {
        return [...acc, ...currentFiles];
      }, []);
      //Remove useless elements
      files = files.filter(currentElement => {
        return currentElement;
      });
      return Promise.all(
        files.map(element => {
          return msgmergeMustache({ element, srcPath });
        })
      );
    });
  });

  /**
   * Extract the js part
   */
  gulp.task("poJs", async () => {
    const info = await getModuleInfo(sourcePath);
    const poConfig = info.buildInfo.build.config["po-config"];
    let poEntry = null;

    if (poConfig) {
      poEntry = poConfig[0]["po-js"];
    }
    if (!poEntry || poEntry.length === 0) {
      log("No JS glob");
      return Promise.resolve();
    }

    //Order glob by target
    const globByTargets = poEntry.reduce((acc, currentElement) => {
      if (!acc[currentElement.$.target]) {
        acc[currentElement.$.target] = [];
      }
      acc[currentElement.$.target].push(currentElement);
      return acc;
    }, {});

    log("Extract JS");
    return Promise.all(
      Object.keys(globByTargets).map(currentKey => {
        return js2Po({
          globFile: analyzeXML(globByTargets[currentKey]),
          targetName: currentKey,
          info,
          potPath,
          verbose,
          log
        });
      })
    );
  });
  /**
   * Extract the php part
   */
  gulp.task("poPhp", async () => {
    const info = await getModuleInfo(sourcePath);
    const poConfig = info.buildInfo.build.config["po-config"];
    let poEntry = null;

    if (poConfig) {
      poEntry = poConfig[0]["po-php"];
    }
    if (!poEntry || poEntry.length === 0) {
      log("No JS glob");
      return Promise.resolve();
    }

    //Order glob by target
    const globByTargets = poEntry.reduce((acc, currentElement) => {
      if (!acc[currentElement.$.target]) {
        acc[currentElement.$.target] = [];
      }
      acc[currentElement.$.target].push(currentElement);
      return acc;
    }, {});

    log("Extract PHP");

    return Promise.all(
      Object.keys(globByTargets).map(currentKey => {
        return php2Po({
          globFile: analyzeXML(globByTargets[currentKey]),
          targetName: currentKey,
          info,
          potPath,
          verbose,
          log
        });
      })
    );
  });
  /**
   * Extract the enum part
   */
  gulp.task("poEnum", async () => {
    const info = await getModuleInfo(sourcePath);
    const srcPath = info.buildInfo.buildPath[0];
    const poConfig = info.buildInfo.build.config["po-config"];
    let globXML = null;

    if (poConfig) {
      globXML = poConfig[0]["po-enum"];
    }
    if (!globXML || globXML.length === 0) {
      log("No enum to extract");
      return Promise.resolve();
    }

    log("Extract enum");
    const globFile = analyzeXML(globXML);

    return xmlEnum2Pot({ globFile, info, potPath, verbose, log }).then(
      files => {
        //Concat files
        return Promise.all(
          files.map(element => {
            return msgmerge({ element, srcPath, potPath, prefix: "enum" });
          })
        );
      }
    );
  });
  /**
   * Extract the smart structure part
   */
  gulp.task("poSmart", async () => {
    const info = await getModuleInfo(sourcePath);
    const srcPath = info.buildInfo.buildPath[0];
    const poConfig = info.buildInfo.build.config["po-config"];
    let globXML = null;

    if (poConfig) {
      globXML = poConfig[0]["po-struct"];
    }
    if (!globXML || globXML.length === 0) {
      log("No smart element to extract");
      return Promise.resolve();
    }

    log("Extract smart element");
    const globFile = analyzeXML(globXML);

    return xmlStructure2Pot({ globFile, info, potPath, verbose, log }).then(
      files => {
        return Promise.all(
          files.map(element => {
            return msgmerge({ element, srcPath, potPath, prefix: "" });
          })
        );
      }
    );
  });

  /**
   * Extract the CVDOC part
   */
  gulp.task("poViewControl", async () => {
    const info = await getModuleInfo(sourcePath);
    const srcPath = info.buildInfo.buildPath[0];
    const poConfig = info.buildInfo.build.config["po-config"];
    let globXML = null;

    if (poConfig) {
      globXML = poConfig[0]["po-cvdoc"];
    }
    if (!globXML || globXML.length === 0) {
      log("No view control to extract");
      return Promise.resolve();
    }

    log("Extract view control element");
    const globFile = analyzeXML(globXML);

    return xmlCVDOC2Pot({ globFile, info, potPath, verbose, log }).then(
      files => {
        return Promise.all(
          files.map(element => {
            return msgmerge({ element, srcPath, potPath, prefix: "cvdoc_" });
          })
        );
      }
    );
  });

  /**
   * Extract the Workflow part
   */
  gulp.task("poWorkflow", async () => {
    const info = await getModuleInfo(sourcePath);
    const srcPath = info.buildInfo.buildPath[0];
    const poConfig = info.buildInfo.build.config["po-config"];
    let globXML = null;

    if (poConfig) {
      globXML = poConfig[0]["po-workflow"];
    }
    if (!globXML || globXML.length === 0) {
      log("No workflow to extract");
      return Promise.resolve();
    }

    log("Extract workflow element");
    const globFile = analyzeXML(globXML);

    return xmlWorkflow2Pot({ globFile, info, potPath, verbose, log }).then(
      files => {
        return Promise.all(
          files.map(element => {
            return msgmerge({ element, srcPath, potPath, prefix: "workflow_" });
          })
        );
      }
    );
  });

  gulp.task("extractPo", async () => {
    // eslint-disable-next-line no-async-promise-executor
    return new Promise(async (resolve, reject) => {
      if (sourcePath === undefined) {
        signale.error("No source path specified.");
        return;
      }
      //Create temp file
      if (!fs.existsSync(potPath)) {
        fs.mkdirSync(potPath);
      }
      try {
        await gulp.task("poSmart")();
        await gulp.task("poEnum")();
        await gulp.task("poViewControl")();
        await gulp.task("poWorkflow")();
        await gulp.task("poMustache")();
        await gulp.task("poPhp")();
        await gulp.task("poJs")();

        deleteFolderRecursive(potPath);
        resolve();
      } catch (e) {
        deleteFolderRecursive(potPath);
        reject(e);
      }
    });
  });
};
