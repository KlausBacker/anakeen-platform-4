const { parseAndConcatGlob } = require("../utils/globAnalyze");
const { checkFile } = require("@anakeen/anakeen-module-validation");

exports.checkGlobElements = ({ globFile, srcPath, verbose, log }) => {
  return parseAndConcatGlob({ globFile, srcPath })
    .then(files => {
      if (verbose) {
        files.ignoredFiles.forEach(currentFile => {
          log(`Analyze : ${currentFile} : in ignore conf`);
        });
      }
      return files.filesToAnalyze.reduce((acc, currentFile) => {
        const checkResult = checkFile(currentFile);
        if (verbose) {
          const result = checkResult.ok ? "✓" : checkResult.ignore ? "ignored" : checkResult.error;
          log(`Analyze : ${currentFile} : ${result}`);
        }
        if (checkResult.error) {
          acc = acc + checkResult.error;
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
