const path = require("path");
const webpack = require("webpack");
const merge = require("webpack-merge");
const { cssLoader, addFalseKendoGlobal, addDll, typeScriptLoader } = require("@anakeen/webpack-conf/parts");
const BASE_PATH = process.env.base ? process.env.base : path.resolve(__dirname, "..");
const OUTPUT_PATH = {
  lib: path.resolve(BASE_PATH, "lib")
};

// User-interfaces relative
const ROOT_DIR = path.resolve(__dirname, "../../");
const PUBLIC_PATH = path.resolve(ROOT_DIR, "src/public");

module.exports = () => {
  const config = {
    entry: {
      AnkController: path.resolve(BASE_PATH, "src/AnakeenController")
    },
    output: {
      libraryTarget: "commonjs2",
      path: OUTPUT_PATH.lib
    },
    resolve: {
      extensions: [".ts", ".js"],
      alias: {
        dcpContextRoot: "",
        dcpDocument: path.resolve(ROOT_DIR, "src/vendor/Anakeen/DOCUMENT/IHM/"),
        dcpExternals: path.resolve(ROOT_DIR, "src/public/uiAssets/externals/"),
        datatables: "datatables.net",
        "datatables-bootstrap": "datatables.net-bs4",
        "kendo-culture-fr": "@progress/kendo-ui/js/cultures/kendo.culture.fr-FR",
        tooltip: "bootstrap/js/src/tooltip",
        documentCkEditor: path.resolve(ROOT_DIR, "webpackConfig/ckeditor/ckeditor.js")
      }
    },
    mode: "development",
    devtool: "inline-source-map",
    plugins: [
      new webpack.ProvidePlugin({
        Popper: ["popper.js", "default"]
      })
    ]
  };
  return merge(
    config,
    typeScriptLoader({
      compilerOptions: {
        declaration: false
      }
    }),
    addDll({
      context: ROOT_DIR,
      manifest: path.join(PUBLIC_PATH, "Anakeen", "assets", "legacy", "KendoUI-manifest.json")
    }),
    addFalseKendoGlobal([/dcpExternals\/KendoUI\/KendoUI/]),
    cssLoader()
  );
};
