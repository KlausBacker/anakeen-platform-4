const path = require("path");
const { dontParse, dllPart, useCache } = require("./dllcommon.part");

const {
  vueLoader,
  typeScriptLoader,
  addKendoGlobal
} = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "Tests/src/public");
const conf = {
  moduleName: "testSmartForm",
  libName: "TestSmartForm",
  entry: {
    "test-smart-form": [
      path.resolve(
        BASE_DIR,
        "Tests/src/vendor/Anakeen/Test/HubComponent/TestSmartForm/index.js"
      )
    ]
  },
  buildPath: PUBLIC_PATH,
  excludeBabel: dontParse,
  customParts: [
    useCache,
    vueLoader(),
    typeScriptLoader(),
    addKendoGlobal([/kendo.pdf/, /kendo.excel/]),
    {
      resolve: {
        extensions: [".js", ".vue", ".json", ".ts", ".tsx"]
      }
    }
  ]
};

conf.customParts = [...conf.customParts, ...dllPart];

module.exports = conf;
