const path = require("path");
const { dontParse, dllPart } = require("./common.part");

const {
  vueLoader,
  typeScriptLoader,
  addFalseKendoGlobal
} = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "src/public");
const conf = {
  moduleName: "teManager",
  libName: "AdminTeManager",
  entry: {
    "te-manager": [
      path.resolve(
        BASE_DIR,
        "src/vendor/Anakeen/TransformationEngine/HubComponent/AdminCenterTeManager/index.js"
      )
    ]
  },
  buildPath: PUBLIC_PATH,
  excludeBabel: dontParse,
  customParts: [
    vueLoader(),
    typeScriptLoader(),
    addFalseKendoGlobal([/kendo.pdf/, /kendo.excel/]),
    {
      resolve: {
        extensions: [".js", ".vue", ".json", ".ts", ".tsx"]
      }
    }
  ]
};

conf.customParts = [...conf.customParts, ...dllPart];

module.exports = conf;