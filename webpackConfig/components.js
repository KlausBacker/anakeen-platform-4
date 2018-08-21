const path = require('path');
const {prod, prodLegacy, dev} = require("@anakeen/webpack-conf");

const BASE_DIR = path.resolve(__dirname, '../');
const PUBLIC_PATH = path.join(BASE_DIR, "anakeen-ui/src/public");

module.exports = () => {
  const conf = {
    moduleName: "ank-components",
    entry: {
      'ank-components': [path.resolve(__dirname, '../anakeen-ui/src/vendor/Anakeen/Components/main.js')]
    },
    buildPath: PUBLIC_PATH,
    customParts: [
      {
        resolve: {
          alias: {
            vue$: 'vue/dist/vue.esm.js',
          },
        },
        output: {
          library: 'ank-components',
          libraryTarget: "umd",
        }
      }
    ]
  };
  const confLegacy = {
    ...conf,
    entry: {
      'ank-components': ['@webcomponents/webcomponentsjs/webcomponents-bundle.js', path.resolve(__dirname, '../anakeen-ui/src/vendor/Anakeen/Components/main.js')]
    },
  };
  const confDev = {
    ...conf,
    entry: {
      'ank-components': [path.resolve(__dirname, '../anakeen-ui/src/vendor/Anakeen/Components/main-debug.js')]
    },
  };
  return [
    prod(conf),
    prodLegacy(confLegacy),
    dev(confDev)
  ];
};