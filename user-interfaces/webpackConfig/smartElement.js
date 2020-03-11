const path = require("path");

const { prod, dev } = require("@anakeen/webpack-conf");
const webpack = require("webpack");
const {
  cssLoader,
  addKendoGlobal,
  addJqueryGlobal,
  typeScriptLoader
} = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "/src/public");

module.exports = () => {
  const conf = {
    context: BASE_DIR,
    moduleName: "smartElement",
    entry: {
      smartElement: [
        path.resolve(
          __dirname,
          "../src/vendor/Anakeen/DOCUMENT/IHM/mainDocument.js"
        )
      ]
    },
    buildPath: PUBLIC_PATH,
    excludeBabel: [/node_modules\/ckeditor/],
    customParts: [
      {
        plugins: [
          new webpack.ProvidePlugin({
            Popper: ["popper.js", "default"]
          })
        ]
      },
      typeScriptLoader({
        compilerOptions: {
          declaration: true
        }
      }),
      addKendoGlobal([/kendo.pdf/, /kendo.excel/], true),
      addJqueryGlobal(),
      cssLoader()
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
