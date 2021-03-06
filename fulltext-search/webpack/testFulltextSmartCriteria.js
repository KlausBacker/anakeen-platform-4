const path = require("path");


const {
  vueLoader,
  typeScriptLoader,
  addKendoGlobal,
  addJqueryGlobal,
  addVueGlobal
} = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "Tests/src/public");
const conf = {
  moduleName: "testFulltextSmartCriteria",
  libName: "testFulltextSmartCriteria",
  entry: {
    "test-fulltext-smart-criteria": [
      path.resolve(
        BASE_DIR,
        "Tests/src/vendor/Anakeen/Test/HubComponent/TestFulltextSmartCriteria/index.js"
      )
    ]
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
    addJqueryGlobal(),
    addVueGlobal(),
    addKendoGlobal([/kendo.pdf/, /kendo.excel/], true),
    {
      resolve: {
        extensions: [".js", ".vue", ".json", ".ts", ".tsx"]
      }
    }
  ]
};

module.exports = conf;
