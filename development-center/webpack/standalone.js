const path = require('path');
const {prod, dev} = require("@anakeen/webpack-conf");
const {
  vueLoader,
  addKendoGlobal
   } = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, '../');
const PUBLIC_PATH =  path.join(BASE_DIR, "src/public");

module.exports = () => {
  const conf = {
    "moduleName": "DevCenterStandalone",
    "entry": {
      'profile': [path.resolve(BASE_DIR, 'src/vendor/Anakeen/DevelopmentCenter/JS/profile.js')],
      'workflow': [path.resolve(BASE_DIR, 'src/vendor/Anakeen/DevelopmentCenter/JS/workflowRights.js')],
    },
    buildPath: PUBLIC_PATH,
    excludeBabel: [
      /node_modules\/axios/,
      /node_modules\/@progress\/.*/,
      /node_modules\/css-loader/,
      /node_modules\/vue/
    ],
    customParts :[
      vueLoader(),
      addKendoGlobal([
        /kendo.pdf/,
        /kendo.excel/
      ], true),
      {
        resolve: {
          alias: {
            devComponents: path.resolve(BASE_DIR, "src/vendor/Anakeen/DevelopmentCenter/vue/components")
          }
        }
      }
    ]
  };
  return [
    prod(conf),
    dev(conf)
  ];
};
