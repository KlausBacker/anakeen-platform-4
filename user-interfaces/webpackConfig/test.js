const path = require("path");
const { prod, dev, lib } = require("@anakeen/webpack-conf");
const {
  vueLoader,
  typeScriptLoader,
  cssLoader,
  addKendoGlobal,
  addJqueryGlobal,
  addVueGlobal
} = require("@anakeen/webpack-conf/parts");
const testSmartForm = require("./testSmartForm");
const testSmartGrid = require("./testSmartGrid");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "Tests/src/public");

module.exports = () => {
  const conf = {
    context: BASE_DIR,
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
      addKendoGlobal([/kendo.pdf/, /kendo.excel/], true),
      addJqueryGlobal(),
      addVueGlobal(),
      cssLoader()
    ]
  };
  if (process.env.conf === "PROD") {
    return [prod(conf), lib(testSmartForm), lib(testSmartGrid)];
  }
  if (process.env.conf === "DEV") {
    return [dev(conf), lib({ ...testSmartForm, ...{ mode: "dev" } }), lib({ ...testSmartGrid, ...{ mode: "dev" } })];
  }
  return [
    prod(conf),
    dev(conf),
    lib(testSmartForm),
    lib({ ...testSmartForm, ...{ mode: "dev" } }),
    lib(testSmartGrid),
    lib({ ...testSmartGrid, ...{ mode: "dev" } })
  ];
};
