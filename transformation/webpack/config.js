const path = require("path");
const webpack = require("webpack");
const { lib } = require("@anakeen/webpack-conf");

const { vueLoader, typeScriptLoader, addKendoGlobal, addJqueryGlobal, addVueGlobal } = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "src/public");

const modeDev = {
  mode: process.env.dev === "DEV" ? "dev" : "prod"
};

const conf = {
  context: BASE_DIR,
  moduleName: "adminTe",
  libName: "adminTe",
  entry: {
    AdminTeManager: [
      path.resolve(BASE_DIR, "src/vendor/Anakeen/TransformationEngine/HubComponent/AdminCenterTeManager/index.js")
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
    addKendoGlobal([/kendo.pdf/, /kendo.excel/], true),
    addJqueryGlobal(),
    addVueGlobal(),
    {
      resolve: {
        extensions: [".js", ".vue", ".json", ".ts", ".tsx"]
      }
    },
    {
      plugins: [
        new webpack.HashedModuleIdsPlugin({
          hashFunction: "sha256",
          hashDigest: "hex",
          hashDigestLength: 20
        })
      ]
    }
  ]
};

module.exports = () => {
  return lib({ ...conf, ...modeDev });
};
