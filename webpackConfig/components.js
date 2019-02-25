const path = require("path");
const { prod, dev } = require("@anakeen/webpack-conf");
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
      setKendoAndJqueryToGlobal([{"./kendo.pdf": "kendo", "./kendo.excel": "kendo"}]),
      vueLoader()
    ]
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
