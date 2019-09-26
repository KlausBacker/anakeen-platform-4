const path = require("path");
const merge = require("webpack-merge");

const baseConfig = require("./base");
const commonConfig = require("./common");

const BASE_PATH = path.resolve(__dirname, "..");
const ENTRIES_PATH = {
  TestTools: path.resolve(BASE_PATH, "src/index.ts")
};

const config = {
  entry: ENTRIES_PATH
};

module.exports = env => {
  return [merge(baseConfig(config), commonConfig(config))];
};
