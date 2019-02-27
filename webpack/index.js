const path = require('path');
const {prod, legacy, dev} = require("@anakeen/webpack-conf");
const {vueLoader, typeScriptLoader, setKendoAndJqueryToGlobal} = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.resolve(BASE_DIR, "src/public");

module.exports = () => {
  const conf = {
    "moduleName": "adminCenter",
    "entry": {
      adminCenter: [ path.resolve(BASE_DIR, "src/vendor/Anakeen/AdminCenter/IHM/main.js")]
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
      setKendoAndJqueryToGlobal([
        /kendo.pdf/,
        /kendo.excel/
      ]),
      {
        resolve: {
          extensions: [".js", ".vue", ".json", ".ts", ".tsx"]
        },
      }
    ]
  };
  if (process.env.conf === "PROD") {
    return prod(conf);
  }
  if (process.env.conf === "DEV") {
    return dev(conf);
  }
  if (process.env.conf === "LEGACY") {
    return legacy(conf);
  }
  return [
    prod(conf),
    legacy(conf),
    dev(conf)
  ];
};