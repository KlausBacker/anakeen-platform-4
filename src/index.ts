const packageInfo = require("../../package.json");

import Vue from "vue";

export function install(vue: typeof Vue) {
  // TODO Register components globally
}

export default {
  install,
  name: packageInfo.name,
  version: packageInfo.version
};
