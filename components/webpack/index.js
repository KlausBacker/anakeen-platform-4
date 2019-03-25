const path = require("path");
const merge = require("webpack-merge");
const { vueLoader, typeScriptLoader } = require("@anakeen/webpack-conf/parts");

const baseConfig = require("./base");
const commonConfig = require("./common");

const BASE_PATH = path.resolve(__dirname, "../");
const ENTRIES_PATH = {
  AnkSEList: path.resolve(BASE_PATH, "src/SEList/seList.vue"),
  AnkLogout: path.resolve(BASE_PATH, "src/Logout/Logout.vue"),
  AnkIdentity: path.resolve(BASE_PATH, "src/Identity/Identity.vue"),
  AnkAuthent: path.resolve(BASE_PATH, "src/Authent/Authent.vue"),
  AnkSEGrid: path.resolve(BASE_PATH, "src/Grid/Grid.vue"),
  AnkSmartElement: path.resolve(BASE_PATH, "src/SmartElement/SmartElement.vue")
};

const config = {
  entry: ENTRIES_PATH
};

module.exports = env => {
  return [merge(baseConfig(config), commonConfig(config), vueLoader())];
};
