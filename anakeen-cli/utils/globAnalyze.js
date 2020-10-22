const glob = require("glob");
const path = require("path");
const signale = require("signale");
const fs = require("fs");

exports.analyzeXML = xmlElementList => {
  return xmlElementList.reduce(
    (acc, currentElement) => {
      if (currentElement.$.ignore) {
        acc.ignoreGlob.push(currentElement.$.source);
      } else {
        acc.addGlob.push(currentElement.$.source);
      }
      return acc;
    },
    {
      addGlob: [],
      ignoreGlob: []
    }
  );
};

exports.analyzeJson = jsonElementGlob => {
  glob(jsonElementGlob, {}, (err, files) => {
    if (files.length > 0) {
      files.forEach(file => {
        try {
          JSON.parse(fs.readFileSync(file));
        } catch (e) {
          signale.error("in file", file);
          throw new Error(e);
        }
      });
    }
  });
};

exports.parseAndConcatGlob = ({ globFile, srcPath }) => {
  return Promise.all([
    Promise.all(
      globFile.addGlob.map(currentGlob => {
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
    ),
    Promise.all(
      globFile.ignoreGlob.map(currentGlob => {
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
  ])
    .then(filesList => {
      const addedFiles = filesList[0].reduce((acc, currentFilesList) => {
        return [...acc, ...currentFilesList];
      }, []);
      const removedFiles = filesList[1].reduce((acc, currentFilesList) => {
        return [...acc, ...currentFilesList];
      }, []);
      return {
        filesToAnalyze: addedFiles.filter(currentFile => {
          return removedFiles.indexOf(currentFile) === -1;
        }),
        ignoredFiles: removedFiles
      };
    })
    .then(filesList => {
      return {
        filesToAnalyze: filesList.filesToAnalyze.map(currentSrc => {
          return path.join(srcPath, currentSrc);
        }),
        ignoredFiles: filesList.ignoredFiles.map(currentSrc => {
          return path.join(srcPath, currentSrc);
        })
      };
    });
};
