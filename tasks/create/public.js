const fs = require("fs");
const path = require("path");

exports.writeTemplate = (packagePath /*, options */) =>
  new Promise((resolve, reject) => {
    fs.mkdir(path.join(packagePath, "src", "public"), err => {
      if (err) {
        return reject(err);
      }
      resolve();
    });
  });
