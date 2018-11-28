const glob = require("glob");
const path = require("path");
const { checkFile } = require("@anakeen/anakeen-module-validation");
/**
 * Analyze the glob array and return a flat list of files
 *
 * @param poGlob
 * @param srcPath
 * @returns {Promise<[any , any , any , any , any , any , any , any , any , any] | never>}
 */
const parseAndConcatGlob = ({ globFile, srcPath }) => {
  return Promise.all(
    globFile.map(currentGlob => {
      return new Promise((resolve, reject) => {
        glob(
          currentGlob,
          {
            cwd: srcPath,
            nodir: true
          },
          (err, files) => {
            if (err) {
              return reject(err);
            }
            resolve(files);
          }
        );
      });
    })
  )
    .then(filesList => {
      return filesList.reduce((acc, currentFilesList) => {
        return [...acc, ...currentFilesList];
      }, []);
    })
    .then(filesList => {
      return filesList.map(currentSrc => {
        return path.join(srcPath, currentSrc);
      });
    });
};

exports.checkGlobElements = ({ globFile, srcPath, verbose, log }) => {
  return parseAndConcatGlob({ globFile, srcPath })
    .then(files => {
      return files.reduce((acc, currentFile) => {
        const checkResult = checkFile(currentFile);
        if (verbose) {
          log(`Analyze : ${currentFile}`);
        }
        if (checkResult !== true) {
          acc = acc + checkResult;
        }
        return acc;
      }, "");
    })
    .then(result => {
      if (result !== "") {
        return Promise.reject(result);
      }
    });
};
