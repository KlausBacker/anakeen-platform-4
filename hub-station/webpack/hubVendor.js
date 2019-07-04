const path = require("path");
const { useCache } = require("./common");
const { deps } = require("@anakeen/webpack-conf");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "src/public");
const USER_INTERFACES = path.resolve(
  BASE_DIR,
  "..",
  "node_modules",
  "@anakeen",
  "user-interfaces"
);
const { vueLoader, addDll, addFalseKendoGlobal } = require("@anakeen/webpack-conf/parts");

module.exports = () => {
  const conf = {
    moduleName: "hubVendor",
    entry: {
      hubVendor: ["@anakeen/user-interfaces/components/lib/AnkSEGrid","@anakeen/user-interfaces/components/lib/AnkLogout","@anakeen/user-interfaces/components/lib/AnkIdentity","@anakeen/user-interfaces/components/lib/AnkSmartElement", "tslib", "vue-property-decorator"]
    },
    excludeBabel: [
      /node_modules\/axios/,
      /node_modules\/@progress\/.*/,
      /node_modules\/css-loader/,
      /node_modules\/vue/
    ],
    buildPath: PUBLIC_PATH,
    customParts: [
      useCache,
      vueLoader(),
      addFalseKendoGlobal([/kendo.pdf/, /kendo.excel/]),
      addDll({
        context: BASE_DIR,
        manifest: path.join(
          USER_INTERFACES,
          "src",
          "public",
          "Anakeen",
          "assets",
          "legacy",
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
          "legacy",
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
          "legacy",
          "ankKendoDll-manifest.json"
        )
      })
    ]
  };
  if (process.env.conf === "DEV") {
    return deps({...conf, ...{mode: "dev"}});
  }
  if (process.env.conf === "LEGACY") {
    return deps(conf);
  }
  return [deps(conf), deps({...conf, ...{mode: "dev"}})];
};
