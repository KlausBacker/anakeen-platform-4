const path = require("path");
const { dontParse, dllPart, useCache } = require("./common.part");

const {
  vueLoader,
  typeScriptLoader,
  addFalseKendoGlobal
} = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "src/public");

const conf = {
  moduleName: "i18nManager",
  libName: "AdminI18nManager",
  entry: {
    "i18n-manager": [
      path.resolve(
        BASE_DIR,
        "src/vendor/Anakeen/AdminCenter/IHM/HubComponent/AdminCenterI18n/index.js"
      )
    ]
  },
  buildPath: PUBLIC_PATH,
  excludeBabel: dontParse,
  customParts: [
    useCache,
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
