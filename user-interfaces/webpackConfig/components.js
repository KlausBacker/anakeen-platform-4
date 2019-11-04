const path = require("path");
const { useCache } = require("./common");
const { prod, legacy, dev } = require("@anakeen/webpack-conf");
const {
  vueLoader,
  typeScriptLoader,
  addFalseKendoGlobal,
  addDll
} = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "/src/public");

module.exports = () => {
  const conf = {
    moduleName: "ank-components",
    entry: {
      "ank-components": [
        path.resolve(__dirname, "../src/vendor/Anakeen/Components/main.js")
      ],
      login: [
        path.resolve(
          __dirname,
          "../src/vendor/Anakeen/Components/Authent/Routes/LoginPage.js"
        )
      ]
    },
    buildPath: PUBLIC_PATH,
    excludeBabel: [
      /node_modules\/axios/,
      /node_modules\/@progress\/kendo-ui/
    ],
    customParts: [
      {
        resolve: {
          alias: {
            "@anakeen/user-interfaces": path.resolve(
              BASE_DIR,
              "components/src/index"
            )
          }
        }
      },
      {
        output: {
          library: "ank-components",
          libraryTarget: "umd"
        }
      },
      addDll({
        context: BASE_DIR,
        manifest: path.join(
          PUBLIC_PATH,
          "Anakeen",
          "assets",
          "legacy",
          "KendoUI-manifest.json"
        )
      }),
      addDll({
        context: BASE_DIR,
        manifest: path.join(
          PUBLIC_PATH,
          "Anakeen",
          "ankDll",
          "legacy",
          "ankKendoDll-manifest.json"
        )
      }),
      addDll({
        context: BASE_DIR,
        manifest: path.join(
          PUBLIC_PATH,
          "Anakeen",
          "ankDll",
          "legacy",
          "vueDll-manifest.json"
        )
      }),
      addFalseKendoGlobal([/kendo.pdf/, /kendo.excel/]),
      vueLoader(),
      typeScriptLoader()
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
  return [prod(conf), legacy(conf), dev(conf)];
};
