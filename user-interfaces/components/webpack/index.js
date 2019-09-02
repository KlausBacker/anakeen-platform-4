const path = require("path");
const merge = require("webpack-merge");
const { vueLoader } = require("@anakeen/webpack-conf/parts");
const controllerConfig = require("./controller");

const baseConfig = require("./base");

const BASE_PATH = path.resolve(__dirname, "../");
const ENTRIES_PATH = {
  AnkLoading: path.resolve(BASE_PATH, "src/AnkLoading/AnkLoading.vue"),
  AnkSEList: path.resolve(BASE_PATH, "src/AnkSEList/AnkSEList.vue"),
  AnkSETabs: path.resolve(BASE_PATH, "src/AnkSETabs/AnkSETabs.vue"),
  AnkSETab: path.resolve(BASE_PATH, "src/AnkSETabs/AnkSETab/AnkSETab.vue"),
  AnkTab: path.resolve(BASE_PATH, "src/AnkSETabs/AnkTab/AnkTab.vue"),
  AnkLogout: path.resolve(BASE_PATH, "src/AnkLogout/AnkLogout.vue"),
  AnkIdentity: path.resolve(BASE_PATH, "src/AnkIdentity/AnkIdentity.vue"),
  AnkAuthent: path.resolve(BASE_PATH, "src/AnkAuthent/AnkAuthent.vue"),
  AnkSEGrid: path.resolve(BASE_PATH, "src/AnkSEGrid/AnkSEGrid.vue"),
  AnkSmartElement: path.resolve(BASE_PATH, "src/AnkSmartElement/AnkSmartElement.vue"),
  AnkSmartForm: path.resolve(BASE_PATH, "src/AnkSmartForm/AnkSmartForm.vue"),
};

const config = {
  entry: ENTRIES_PATH
};

module.exports = env => {
  if (process.env.conf === "CONTROLLER") {
    return controllerConfig();
  }
  return [merge(config, baseConfig(), vueLoader())];
};
