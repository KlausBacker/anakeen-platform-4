const fs = require("fs");
const path = require("path");

const exportObject = {};

const files = fs.readdirSync(path.resolve(__dirname));

if (files && files.length) {
  files.forEach(file => {
    const statFile = fs.statSync(path.resolve(__dirname, file));
    if (statFile.isFile()) {
      if (file !== "index.js" && path.extname(file) === ".js") {
        exportObject[file.replace(/\.js$/, "")] = require(`./${file}`);
      }
    } else if (statFile.isDirectory()) {
      if (fs.existsSync(path.resolve(__dirname, file, "index.js"))) {
        exportObject[file] = require(`./${file}`);
      }
    }
  });
}

module.exports = {
  ...exportObject
};
