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
  buildPath: PUBLIC_PATH,
  customParts: [
    vueLoader(),
    typeScriptLoader(),
    addFalseKendoGlobal([/kendo.pdf/, /kendo.excel/]),
    {
      resolve: {
        extensions: [".js", ".vue", ".json", ".ts", ".tsx"]
      }
    }
  ],
  entry: {
    "business-app": [
      path.resolve(BASE_DIR, "src/vendor/Anakeen/BusinessApp/IHM/index.ts")
    ]
  },
  excludeBabel: dontParse,
  libName: "HubBusinessApp",
  moduleName: "businessApp"
};

conf.customParts = [...conf.customParts, ...dllPart];

module.exports = conf;
