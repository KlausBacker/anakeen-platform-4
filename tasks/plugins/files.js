const fs = require("fs");
const path = require("path");

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
 * Write multiple files asynchronously.
 * @param {Array<{path: string, content: string}>}files
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
