const path = require("path");
const { useCache } = require("./common");
const { prod, dev } = require("@anakeen/webpack-conf");
const {
  vueLoader,
  typeScriptLoader,
  addKendoGlobal,
  addJqueryGlobal,
  addVueGlobal
} = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "src/public");

module.exports = () => {
  const conf = {
    moduleName: "hub",
    entry: {
      hub: [path.resolve(BASE_DIR, "src/vendor/Anakeen/Hub/IHM/JS/hub.js")]
    },
    excludeBabel: [
      /node_modules\/axios/,
      /node_modules\/@progress\/.*/,
      /node_modules\/css-loader/,
      /node_modules\/vue/
    ],
    buildPath: PUBLIC_PATH,
    customParts: [
      useCache,
      addJqueryGlobal(),
      addKendoGlobal([/kendo.pdf/, /kendo.excel/], true),
      vueLoader(),
      typeScriptLoader(),
      {
        resolve: {
          alias: {
            "@anakeen/hub-components/lib/AnkHubStation.esm": path.resolve(
              BASE_DIR,
              "components/lib/AnkHubStation.esm.js"
            ),
            "@anakeen/hub-components/lib/AnkHubUtil.esm": path.resolve(
              BASE_DIR,
              "components/lib/AnkHubUtil.esm.js"
            )
          }
        }
      },
      {
        module: {
          rules: [
            {
              test: /\.(ttf|eot|woff|woff2)$/,
              use: {
                loader: "file-loader"
              }
            }
          ]
        }
      }
    ]
  };
  if (process.env.conf === "PROD") {
    return prod(conf);
  }
  if (process.env.conf === "DEV") {
    return dev(conf);
  }
  return [prod(conf), dev(conf)];
};
