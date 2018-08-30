const path = require('path');
const {deps} = require("@anakeen/webpack-conf");

const BASE_DIR = path.resolve(__dirname, '../');
const PUBLIC_PATH =  path.join(BASE_DIR, "anakeen-ui/src/public");

module.exports = () => {
  const conf = {
    moduleName: "polyfill",
    entry: {
      'polyfill': [path.resolve(__dirname,'./scriptModulePolyfill.js'), 'whatwg-fetch', 'core-js']
    },
    buildPath: PUBLIC_PATH,
    customParts: [
    ]
  };
  return [
    deps(conf)
  ]
};