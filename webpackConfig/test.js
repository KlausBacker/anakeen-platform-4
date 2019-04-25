const path = require("path");
const { prod, dev, legacy } = require("@anakeen/webpack-conf");
const { vueLoader,typeScriptLoader, cssLoader, addDll } = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "Tests/src/public");
const USER_INTERFACES = path.join(BASE_DIR, "/src/public");

module.exports = () => {
  const conf = {
    moduleName: "uiTest",
    entry: {
      familyTestRender: [
        path.resolve(
          __dirname,
          "../Tests/src/vendor/Anakeen/SmartStructures/UiTest/TestRender/testRender.js"
        )
      ]
    },
    buildPath: PUBLIC_PATH,
    customParts: [
      vueLoader(),
      typeScriptLoader(),
      addDll({
        context: BASE_DIR,
        manifest: path.join(
          USER_INTERFACES,
          "Anakeen",
          "assets",
          "legacy",
          "KendoUI-manifest.json"
        )
      }),
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
