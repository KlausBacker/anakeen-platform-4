const path = require('path');
const {prod, prodLegacy, dev} = require("@anakeen/webpack-conf");
const {cssLoader} = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, '../');
const PUBLIC_PATH = path.join(BASE_DIR, "Tests/src/public");

module.exports = () => {
  const conf = {
    moduleName: "uiTest",
    entry: {
      familyTestRender: [path.resolve(__dirname, '../Tests/src/vendor/Anakeen/SmartStructures/UiTest/TestRender/testRender.js')],
      TestPage: [path.resolve(__dirname, '../Tests/src/vendor/Anakeen/Routes/UiTest/TestPage.js')]
    },
    buildPath: PUBLIC_PATH,
    customParts: [
      cssLoader()
    ]
  };
  return [
    prod(conf),
    prodLegacy(conf),
    dev(conf)
  ];
};