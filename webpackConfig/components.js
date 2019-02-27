const path = require("path");
const { prod, legacy, dev } = require("@anakeen/webpack-conf");
const {
  vueLoader,
  setKendoAndJqueryToGlobal
} = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "/src/public");

module.exports = () => {
  const conf = {
    moduleName: "ank-components",
    entry: {
      "ank-components": [
        path.resolve(__dirname, "../src/vendor/Anakeen/Components/main.js")
      ]
    },
    buildPath: PUBLIC_PATH,
    excludeBabel: [
      /node_modules\/axios/,
      /node_modules\/@progress\/kendo-ui/,
      /node_modules\/document-register-element/
    ],
    customParts: [
      {
        output: {
          library: "ank-components",
          libraryTarget: "umd"
        }
      },
      setKendoAndJqueryToGlobal([/kendo.pdf/, /kendo.excel/]),
      vueLoader()
    ]
  };
  return [prod(conf), legacy(conf), dev(conf)];
};
