const path = require('path');
const {prod, dev} = require("@anakeen/webpack-conf");
const { useVueLoader, setKendoAndJqueryToGlobal } = require("@anakeen/webpack-conf/parts");


const BASE_DIR = path.resolve(__dirname, '../');
const PUBLIC_PATH =  path.join(BASE_DIR, "src/public");

module.exports = () => {
  const conf = {
    "moduleName": "developmentCenter",
    "entry": {
      'main': [path.resolve(BASE_DIR, 'src/vendor/Anakeen/DevelopmentCenter/JS/main.js')]
    },
    buildPath: PUBLIC_PATH,
    customParts :[
      useVueLoader(),
      setKendoAndJqueryToGlobal([
        /kendo.pdf/,
        /kendo.excel/
      ]),
    ]
  };
  return [
    prod(conf),
    dev(conf)
  ];
};
