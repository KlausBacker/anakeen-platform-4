const path = require("path");
const { useCache } = require("./common");
const { prod, dev, legacy } = require("@anakeen/webpack-conf");
const {
  vueLoader,
  typeScriptLoader,
  addFalseKendoGlobal,
  addDll
} = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../");
const USER_INTERFACES = path.resolve(
  BASE_DIR,
  "..",
  "node_modules",
  "@anakeen",
  "user-interfaces"
);
const PUBLIC_PATH = path.join(BASE_DIR, "src/public");

module.exports = () => {
  const conf = {
    moduleName: "hub",
    entry: {
      hub: [path.resolve(BASE_DIR, "src/vendor/Anakeen/Hub/IHM/JS/hub.js")],
      hubAdmin: [
        path.resolve(BASE_DIR, "src/vendor/Anakeen/Hub/IHM/JS/hubAdmin.js")
      ],
      hubInstanciation: [
        path.resolve(
          BASE_DIR,
          "src/vendor/Anakeen/Hub/IHM/JS/hubInstanciation.js"
        )
      ]
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
      }),
      addDll({
        context: BASE_DIR,
        manifest: path.join(
          PUBLIC_PATH,
          "Anakeen",
          "hubVendor",
          "legacy",
          "hubVendor-manifest.json"
        )
      }),
      addFalseKendoGlobal([/kendo.pdf/, /kendo.excel/]),
      vueLoader(),
      typeScriptLoader(),
      {
        resolve: {
          alias: {
            "@anakeen/hub-components/lib/HubStation": path.resolve(
              BASE_DIR,
              "components/src/HubStation/HubStation.vue"
            ),
            "@anakeen/hub-components/lib/HubEntriesUtil": path.resolve(
              BASE_DIR,
              "components/src/utils/HubEntriesUtil.js"
            )
          }
        }
      },
      {
        module: {
          rules: [
            {
              test: /\.(ttf|eot|woff|woff2)$/,
              use: {
                loader: "file-loader"
              }
            }
          ]
        }
      }
    ]
  };
  if (process.env.conf === "PROD") {
    return prod(conf);
  }
  if (process.env.conf === "DEV") {
    return dev(conf);
  }
  if (process.env.conf === "LEGACY") {
    return legacy(conf);
  }
  return [prod(conf), dev(conf), legacy(conf)];
};