const fs = require("fs");
const path = require("path");

exports.writeTemplate = ({ sourcePath }) =>
  new Promise((resolve, reject) => {
    fs.mkdir(path.join(sourcePath, "src", "public"), err => {
      if (err) {
        return reject(err);
      }
      resolve();
    });
  });
