const path = require("path");
const { useCache } = require("./common");
const { prod, dev, legacy } = require("@anakeen/webpack-conf");
const { cssLoader, addKendoGlobal, addJqueryGlobal } = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "src/public");

module.exports = () => {
  const conf = {
    moduleName: "hubRender",
    entry: {
      hubConfiguration: [
        path.resolve(BASE_DIR, "src/vendor/Anakeen/Hub/SmartStructures/HubConfiguration/Render/HubConfiguration.js")
      ],
      hubInstanciationRender: [
        path.resolve(BASE_DIR, "src/vendor/Anakeen/Hub/SmartStructures/HubInstanciation/Render/HubInstanciation.js")
      ]
    },
    excludeBabel: [
      /node_modules\/axios/,
      /node_modules\/@progress\/.*/,
      /node_modules\/css-loader/,
      /node_modules\/vue/
    ],
    buildPath: PUBLIC_PATH,
    customParts: [useCache, addJqueryGlobal(), addKendoGlobal([/kendo.pdf/, /kendo.excel/], true), cssLoader()]
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
