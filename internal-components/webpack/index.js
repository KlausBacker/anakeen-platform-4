const path = require("path");
const merge = require("webpack-merge");

const baseConfig = require("./base");
const commonConfig = require("./common");

const BASE_PATH = path.resolve(__dirname, "..");
const ENTRIES_PATH = {
  AxiosPlugin: path.resolve(BASE_PATH, "src/AxiosPlugin/AxiosPlugin.ts"),
  Notifier: path.resolve(BASE_PATH, "src/Notifier/Notifier.vue"),
  Splitter: path.resolve(BASE_PATH, "src/Splitter/Splitter.vue")
};

const config = {
  entry: ENTRIES_PATH
};

module.exports = env => {
  return [merge(baseConfig(config), commonConfig(config))];
};
