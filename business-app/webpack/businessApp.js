const path = require("path");

const {
  vueLoader,
  typeScriptLoader,
  addKendoGlobal,
  addJqueryGlobal
} = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "src/public");

const conf = {
  buildPath: PUBLIC_PATH,
  customParts: [
    vueLoader(),
    typeScriptLoader(),
    addKendoGlobal([/kendo.pdf/, /kendo.excel/], true),
    addJqueryGlobal(),
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
  excludeBabel: [
    /node_modules\/axios/,
    /node_modules\/@progress\/.*/,
    /node_modules\/css-loader/,
    /node_modules\/vue/
  ],
  libName: "HubBusinessApp",
  moduleName: "businessApp"
};

module.exports = conf;
