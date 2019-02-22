const path = require("path");
const { prod, prodLegacy, dev } = require("@anakeen/webpack-conf");
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
    customParts: [
      {
        resolve: {
          alias: {
            vue$: "vue/dist/vue.esm.js"
          }
        },
        output: {
          library: "ank-components",
          libraryTarget: "umd"
        }
      },
      setKendoAndJqueryToGlobal([/kendo.pdf/, /kendo.excel/]),
      vueLoader()
    ]
  };
  const confLegacy = {
    ...conf,
    entry: {
      "ank-components": [
        "@webcomponents/webcomponentsjs/webcomponents-bundle.js",
        path.resolve(__dirname, "../src/vendor/Anakeen/Components/main.js")
      ]
    }
  };
  const confDev = {
    ...conf,
    entry: {
      "ank-components": [
        path.resolve(
          __dirname,
          "../src/vendor/Anakeen/Components/main-debug.js"
        )
      ]
    }
  };
  return [prod(conf), dev(confDev)];
};
