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

exports.generateSetting = (settingPath, argv) => {
  if (argv.type) {
    if (!exportObject[argv.type] || !exportObject[argv.type].writeTemplate) {
      return Promise.reject(`The type ${argv.type} is unknown or must be implemented`);
    }
    return exportObject[argv.type].writeTemplate(settingPath, argv);
  } else {
    return Promise.reject("A setting type must be provided");
  }
};
