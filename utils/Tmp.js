const tmp = require("tmp");

class Tmp {
  static async tmpfile(options) {
    return new Promise((resolve, reject) => {
      tmp.file(options, (err, name, fd, cleanupCb) => {
        if (err) {
          reject(err);
        } else {
          resolve({ name, fd, cleanupCb });
        }
      });
    });
  }
}

module.exports = Tmp;