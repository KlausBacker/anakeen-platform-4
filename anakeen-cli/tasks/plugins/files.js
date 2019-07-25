const fs = require("fs");
const path = require("path");
const mustache = require("mustache");

/**
 * Create a dir asynchronously (and parents dir if they not exist)
 * @param dirPath
 * @param callback
 */
exports.mkpdir = (dirPath, callback) => {
  // Private function to keep dirPath in the high level function context
  const _makeparentdir = (parentPath, cb) => {
    const dirname = path.dirname(parentPath);
    if (!fs.existsSync(dirname)) {
      _makeparentdir(dirname, () => {
        // Parent dir is created, create subdir
        fs.mkdir(parentPath, err => {
          if (parentPath === dirPath) {
            if (typeof callback === "function") {
              callback(err);
            }
          } else {
            cb(err);
          }
        });
      });
    } else {
      // If parent exists, create subdir
      fs.mkdir(parentPath, err => {
        if (parentPath === dirPath) {
          if (typeof callback === "function") {
            callback(err);
          }
        } else {
          cb(err);
        }
      });
    }
  };
  // Call private function
  _makeparentdir(dirPath);
};

/**
 * Create multiple dirs asynchronously.
 * @param {...string} dirpaths
 * @return {Promise<any>[]}
 */
exports.mkdirs = (...dirpaths) => {
  if (dirpaths && dirpaths.length) {
    return Promise.all(
      dirpaths.map(dirpath => {
        return new Promise((resolve, reject) => {
          fs.mkdir(dirpath, err => {
            if (err) {
              reject(err);
            } else {
              resolve();
            }
          });
        });
      })
    );
  }
  return Promise.resolve();
};

/**
 * Write multiple files asynchronously.
 * @param {...{path: string, content: string}}files
 * @return {Promise<any>[]}
 */
exports.writeFiles = (...files) => {
  if (files && files.length) {
    return Promise.all(
      files.map(file => {
        return new Promise((resolve, reject) => {
          fs.writeFile(file.path, file.content, err => {
            if (err) {
              reject(err);
            } else {
              resolve();
            }
          });
        });
      })
    );
  }
  return Promise.resolve();
};

/**
 * Write template
 */
const writeTemplate = (destinationPath, templateFile, templateData = {}) => {
  return new Promise((resolve, reject) => {
    if (!fs.existsSync(path.dirname(destinationPath))) {
      reject(`The destination path "${destinationPath}" does not exist`);
    } else if (!fs.existsSync(templateFile)) {
      reject(`The template file "${templateFile}" does not exist`);
    } else {
      fs.readFile(templateFile, "utf8", (err, content) => {
        if (err) {
          reject(err);
        } else {
          fs.writeFile(destinationPath, mustache.render(content, templateData), err => {
            if (err) {
              reject(err);
            } else {
              resolve(destinationPath);
            }
          });
        }
      });
    }
  });
};
exports.writeTemplate = writeTemplate;
/**
 *
 * @param {...{ destinationPath: string, templateFile: string, templateData: object }} configs
 */
exports.writeTemplates = (...configs) => {
  const promises = [];
  configs.forEach(config => {
    if (!(config.destinationPath && config.templateFile)) {
      promises.push(Promise.reject("The given configuration for writing the template is invalid"));
    } else {
      promises.push(writeTemplate(config.destinationPath, config.templateFile, config.templateData || {}));
    }
  });
  return Promise.all(promises);
};
