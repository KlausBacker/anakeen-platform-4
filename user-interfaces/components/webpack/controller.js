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
      AnkController: path.resolve(BASE_PATH, "src/AnkController")
    },
    output: {
      libraryTarget: "commonjs2",
      path: OUTPUT_PATH.lib
    },
    resolve: {
      extensions: [".ts", ".js"]
    },
    mode: "development",
    devtool: "inline-source-map",
  };
  return merge(
    config,
    typeScriptLoader({
      compilerOptions: {
        declaration: true,
        declarationDir: path.resolve(__dirname, "../lib"),
      }
    }),
  );
};
