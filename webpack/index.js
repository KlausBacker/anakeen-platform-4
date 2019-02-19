const path = require("path");
const merge = require("webpack-merge");

const baseConfig = require("./base");
const prodConfig = require("./prod");
const devConfig = require("./dev");
const commonConfig = require("./common");
const umdConfig = require("./umd");

const BASE_PATH = path.resolve(__dirname, "..");
const ENTRIES_PATH = {
  lib: path.resolve(BASE_PATH, "src/index.ts")
};

const config = {
  entry: {
    "ank-internal-components": ENTRIES_PATH.lib
  }
};

module.exports = env => {
  if (env === "production") {
    return [
      merge(baseConfig(config), prodConfig(config), commonConfig(config)),
      merge(baseConfig(config), prodConfig(config), umdConfig(config))
    ];
  }
  return [
    merge(baseConfig(config), devConfig(config), commonConfig(config)),
    merge(baseConfig(config), devConfig(config), umdConfig(config))
  ];
};
