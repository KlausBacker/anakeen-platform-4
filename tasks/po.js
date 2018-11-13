const gulp = require("gulp");
const {
  xmlStructure2Pot,
  xmlEnum2Pot,
  php2Po,
  js2Po,
  msgmergeStructure,
  msgmergeMustache,
  msgmergeEnum
} = require("../utils/POExtractor");
const { getModuleInfo } = require("../utils/moduleInfo");
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

exports.po = ({ sourcePath }) => {
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
    if (!poEntry) {
      log("No mustache template to extract");
      return new Promise.resolve();
    }

    log("Extract Mustache template");
    return Promise.all(
      poEntry.map(item => {
        return mustache2Pot(item.$.source, item.$.target, info, potPath);
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
    let poJs = null;

    if (poConfig) {
      poJs = poConfig[0]["po-js"];
    }
    if (!poJs) {
      log("No JS to extract");
      return new Promise.resolve();
    }

    log("Extract JS");
    return Promise.all(
      poJs.map(jsItem => {
        return js2Po(jsItem.$.source, jsItem.$.target, info, potPath);
      })
    );
  });
  /**
   * Extract the php part
   */
  gulp.task("poPhp", async () => {
    const info = await getModuleInfo(sourcePath);
    const poConfig = info.buildInfo.build.config["po-config"];
    let poPhp = null;

    if (poConfig) {
      poPhp = poConfig[0]["po-php"];
    }
    if (!poPhp) {
      log("No PHP to extract");
      return Promise.resolve();
    }

    log("Extract PHP");

    return Promise.all(
      poPhp.map(jsItem => {
        return php2Po({
          phpGlob: jsItem.$.source,
          target: jsItem.$.target,
          info,
          potPath
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
    if (!globXML) {
      log("No enum to extract");
      return Promise.resolve();
    }

    log("Extract enum");
    const poGlob = globXML.map(currentElement => {
      return currentElement.$.source;
    });

    return xmlEnum2Pot({ poGlob, info, potPath }).then(files => {
      //Remove useless elements
      files = files.filter(currentElement => {
        return currentElement;
      });
      return Promise.all(
        files.map(element => {
          return msgmergeEnum({ element, srcPath, potPath });
        })
      );
    });
  });
  /**
   * Extract the smart structure part
   */
  gulp.task("poSmart", async () => {
    const info = await getModuleInfo(sourcePath);
    const srcPath = info.buildInfo.buildPath[0];
    const poConfig = info.buildInfo.build.config["po-config"];
    let poStruct = null;

    if (poConfig) {
      poStruct = poConfig[0]["po-struct"];
    }
    if (!poStruct) {
      log("No smart element to extract");
      return Promise.resolve();
    }

    log("Extract smart element");
    const poGlob = poStruct.map(currentElement => {
      return currentElement.$.source;
    });

    return xmlStructure2Pot({ poGlob, info, potPath }).then(files => {
      //Remove useless elements
      files = files.filter(currentElement => {
        return currentElement;
      });
      return Promise.all(
        files.map(element => {
          return msgmergeStructure({ element, srcPath, potPath });
        })
      );
    });
  });

  gulp.task("extractPo", async () => {
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
