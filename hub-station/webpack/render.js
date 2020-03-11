const path = require("path");
const { prod, dev } = require("@anakeen/webpack-conf");
const { cssLoader, addKendoGlobal, addJqueryGlobal } = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "src/public");

module.exports = () => {
  const conf = {
    context: BASE_DIR,
    moduleName: "hubRender",
    entry: {
      hubConfiguration: [
        path.resolve(BASE_DIR, "src/vendor/Anakeen/Hub/SmartStructures/HubConfiguration/Render/HubConfiguration.js")
      ],
      hubInstanciationRender: [
        path.resolve(BASE_DIR, "src/vendor/Anakeen/Hub/SmartStructures/HubInstanciation/Render/HubInstanciation.js")
      ]
    },
    buildPath: PUBLIC_PATH,
    customParts: [addJqueryGlobal(), addKendoGlobal([/kendo.pdf/, /kendo.excel/], true), cssLoader()]
  };
  if (process.env.conf === "PROD") {
    return prod(conf);
  }
  if (process.env.conf === "DEV") {
    return dev(conf);
  }
  return [prod(conf), dev(conf)];
};
