const path = require("path");
const { useCache } = require("./common");
const { prod, dev, legacy, lib } = require("@anakeen/webpack-conf");
const {
  vueLoader,
  typeScriptLoader,
  cssLoader,
  addDll
} = require("@anakeen/webpack-conf/parts");
const testSmartForm = require("./testSmartForm");

// const EsmWebpackPlugin = require("@purtuga/esm-webpack-plugin");

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
      useCache,
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
      cssLoader(),
      {
        output: {
          library: "familyTestRender",
          libraryTarget: "var",
          libraryExport: "default"
        }
        // plugins: [
        //   new EsmWebpackPlugin()
        // ]
      }
    ]
  };
  if (process.env.conf === "PROD") {
    return [prod(conf), lib(testSmartForm)];
  }
  if (process.env.conf === "DEV") {
    return [dev(conf), lib({ ...testSmartForm, ...{ mode: "dev" } })];
  }
  if (process.env.conf === "LEGACY") {
    return legacy(conf);
  }
  return [
    prod(conf),
    dev(conf),
    legacy(conf),
    lib(testSmartForm),
    lib({ ...testSmartForm, ...{ mode: "dev" } })
  ];
};
