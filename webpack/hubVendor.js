const path = require("path");
const { deps } = require("@anakeen/webpack-conf");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "src/public");
const USER_INTERFACES = path.resolve(
  BASE_DIR,
  "node_modules",
  "@anakeen",
  "user-interfaces"
);
const { vueLoader, addDll } = require("@anakeen/webpack-conf/parts");

module.exports = () => {
  const conf = {
    moduleName: "hubVendor",
    entry: {
      hubVendor: ["@anakeen/user-interfaces", "tslib", "vue-property-decorator"]
    },
    excludeBabel: [
      /node_modules\/axios/,
      /node_modules\/@progress\/.*/,
      /node_modules\/css-loader/,
      /node_modules\/vue/
    ],
    buildPath: PUBLIC_PATH,
    customParts: [
      vueLoader(),
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
      })
    ]
  };
  return [deps(conf)];
};
