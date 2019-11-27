const path = require("path");
const { prod, dev, legacy } = require("@anakeen/webpack-conf");
const {
  cssLoader,
  addKendoGlobal,
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
    moduleName: "businessAppRender",
    entry: {
      businessApp: [
        path.resolve(
          BASE_DIR,
          "src/vendor/Anakeen/BusinessApp/SmartStructures/HubBusinessApp/Render/HubBusinessApp.js"
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
          "legacy",
          "KendoUI-manifest.json"
        )
      }),
      addKendoGlobal([/kendo.pdf/, /kendo.excel/]),
      cssLoader()
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
