const path = require("path");
const { prod, dev, legacy } = require("@anakeen/webpack-conf");
const {
  vueLoader,
  setKendoAndJqueryToGlobal,
  addDll
} = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../");
const USER_INTERFACES = path.resolve(
  BASE_DIR,
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
        "@progress/kendo-ui/js/kendo.splitter",
        path.resolve(BASE_DIR, "src/vendor/Anakeen/Hub/IHM/JS/hubAdmin.js")
      ],
      hubInstanciation: [
        "@progress/kendo-ui/js/kendo.splitter",
        path.resolve(
          BASE_DIR,
          "src/vendor/Anakeen/Hub/IHM/JS/hubInstanciation.js"
        )
      ],
      hubConfiguration: [
        path.resolve(
          BASE_DIR,
          "src/vendor/Anakeen/Hub/SmartStructures/HubConfiguration/Render/HubConfiguration.js"
        )
      ],
      hubInstanciationRender: [
        path.resolve(
          BASE_DIR,
          "src/vendor/Anakeen/Hub/SmartStructures/HubInstanciation/Render/HubInstanciation.js"
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
          PUBLIC_PATH,
          "Anakeen",
          "hubVendor",
          "deps",
          "hubVendor-manifest.json"
        )
      }),
      setKendoAndJqueryToGlobal([/kendo.pdf/, /kendo.excel/]),
      vueLoader(),
      {
        resolve: {
          alias: {
            "@anakeen/hub-components": path.resolve(
              BASE_DIR,
              "components/lib/hub-components.common.min.js"
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
