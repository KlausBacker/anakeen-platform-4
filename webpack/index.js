const path = require('path');
const {prod, dev} = require("@anakeen/webpack-conf");
const { useVueLoader, setKendoAndJqueryToGlobal } = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, '../');
const PUBLIC_PATH =  path.join(BASE_DIR, "src/public");

module.exports = () => {
  const conf = {
    "moduleName": "developmentCenter",
    "entry": {
      'main': [path.resolve(BASE_DIR, 'src/vendor/Anakeen/DevelopmentCenter/JS/main.js')],
      'profile': [path.resolve(BASE_DIR, 'src/vendor/Anakeen/DevelopmentCenter/JS/profile.js')],
    },
    buildPath: PUBLIC_PATH,
    customParts :[
      useVueLoader(),
      setKendoAndJqueryToGlobal([
        /kendo.pdf/,
        /kendo.excel/
      ]),
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
