const path = require("path");
const BASE_DIR = path.resolve(__dirname, "../");
const USER_INTERFACES = path.resolve(BASE_DIR,
 "node_modules",
 "@anakeen",
 "user-interfaces");
const HUB = path.resolve(BASE_DIR,
"node_modules",
 "@anakeen",
 "hub-components");

const { addDll } = require("@anakeen/webpack-conf/parts");

module.exports.dontParse = [
  /node_modules\/axios/,
  /node_modules\/@progress\/.*/,
  /node_modules\/css-loader/,
  /node_modules\/vue/,
  /node_modules\/jsoneditor/,
  /node_modules\/brace/
];

module.exports.dllPart = [
  addDll({
    context: BASE_DIR,
    manifest: path.join(
      USER_INTERFACES,
      "src",
      "public",
      "Anakeen",
      "assets",
      "deps",
      "KendoUI-manifest.json"
    )
  }),
  addDll({
    context: BASE_DIR,
    manifest: path.join(
      USER_INTERFACES,
      "src",
      "public",
      "Anakeen",
      "ankDll",
      "deps",
      "vueDll-manifest.json"
    )
  }),
  addDll({
    context: BASE_DIR,
    manifest: path.join(
      USER_INTERFACES,
      "src",
      "public",
      "Anakeen",
      "ankDll",
      "deps",
      "ankKendoDll-manifest.json"
    )
  }),
  addDll({
    context: BASE_DIR,
    manifest: path.join(
      HUB,
      "src",
      "public",
      "Anakeen",
      "hubVendor",
      "deps",
      "hubVendor-manifest.json"
    )
  })
];
