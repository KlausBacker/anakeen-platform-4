const path = require("path");

const {
  vueLoader,
  typeScriptLoader,
  setKendoAndJqueryToGlobal
} = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "src/public");
module.exports = {
    moduleName: "vaultManager",
    libName: "AdminVaultManager",
    entry: {
      "vault-manager": [path.resolve(BASE_DIR, "src/vendor/Anakeen/AdminCenter/HubComponent/AdminCenterVaultManager/index.js")]
    },
    buildPath: PUBLIC_PATH,
  excludeBabel: [
    /node_modules\/axios/,
    /node_modules\/@progress\/.*/,
    /node_modules\/css-loader/,
    /node_modules\/vue/,
    /node_modules\/jsoneditor/,
    /node_modules\/brace/
  ],
  customParts: [
    vueLoader(),
    typeScriptLoader(),
    setKendoAndJqueryToGlobal([/kendo.pdf/, /kendo.excel/]),
    {
      resolve: {
        extensions: [".js", ".vue", ".json", ".ts", ".tsx"]
      }
    }
  ]
};