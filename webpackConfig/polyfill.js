const path = require("path");
const { legacy } = require("@anakeen/webpack-conf");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "src/public");

module.exports = () => {
  const conf = {
    moduleName: "polyfill",
    entry: {
      polyfill: ['core-js/features/promise', 'whatwg-fetch']
    },
    buildPath: PUBLIC_PATH
  };
  if (process.env.conf === "LEGACY") {
    return legacy(conf);
  }

  return [legacy(conf)];
};
