const path = require("path");
const merge = require("webpack-merge");

const baseConfig = require("./base");
const commonConfig = require("./common");

const BASE_PATH = path.resolve(__dirname, "..");
const ENTRIES_PATH = {
  HubElement: path.resolve(BASE_PATH, "src/HubElement/HubElement.vue"),
  HubElementMixin: path.resolve(BASE_PATH, "src/HubElement/Mixins/index.ts"),
  HubStation: path.resolve(BASE_PATH, "src/HubStation/HubStation.vue")
};

const config = {
  entry: ENTRIES_PATH
};

module.exports = env => {
  return [merge(baseConfig(config), commonConfig(config))];
};
