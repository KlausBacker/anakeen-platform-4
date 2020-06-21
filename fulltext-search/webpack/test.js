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
const testFulltextSmartCriteria = require("./testFulltextSmartCriteria");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "Tests/src/public");

module.exports = () => {
  const conf = {
    context: BASE_DIR,
    moduleName: "fulltextTest",
    entry: {},
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
    return [lib(testFulltextSmartCriteria)];
  }
  if (process.env.conf === "DEV") {
    return [lib({ ...testFulltextSmartCriteria, ...{ mode: "dev" } })];
  }
  return [
    lib(testFulltextSmartCriteria),
    lib({ ...testFulltextSmartCriteria, ...{ mode: "dev" } })
  ];
};
