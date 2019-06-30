const path = require("path");
const { findApp, getInfoFromApp } = require("./utils/appUtils.js");

const appPath = process.argv[2];
const appName = process.argv[3];

findApp(appName, appPath).then(fileName => {
  getInfoFromApp(path.join(appPath, fileName)).then(appInfo => {
    console.log(appInfo.version);
  });
});
