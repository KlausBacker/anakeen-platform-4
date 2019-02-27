const path = require("path");
const { deps } = require("@anakeen/webpack-conf");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "/src/public");

module.exports = () => {
  const conf = {
    moduleName: "polyfill",
    entry: {
      polyfill: [
        path.resolve(__dirname, "./polyfill/scriptModulePolyfill.js"),
        path.resolve(__dirname, "./polyfill/promisePolyfill.js"),
        "whatwg-fetch"
      ]
    },
    withoutBabel: true,
    buildPath: PUBLIC_PATH,
    customParts: []
  };
  return [deps(conf)];
};
