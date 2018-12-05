const fs = require("fs");
const path = require("path");

const exportObject = {};

const files = fs.readdirSync(path.resolve(__dirname));

if (files && files.length) {
  files.forEach(file => {
    if (file !== "index.js" && path.extname(file) === ".js") {
      exportObject[file.replace(/\.js$/, "")] = require(`./${file}`);
    }
  });
}

module.exports = {
  ...exportObject
};
