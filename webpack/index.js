const path = require('path');
const {prod, prodLegacy, dev} = require("@anakeen/webpack-conf");
const {useVueLoader, typescriptLoader, setKendoAndJqueryToGlobal} = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.resolve(BASE_DIR, "src/public");

module.exports = () => {
  const conf = {
    "moduleName": "adminCenter",
    "entry": {
      adminCenter: [ path.resolve(BASE_DIR, "src/vendor/Anakeen/AdminCenter/IHM/main.js")]
    },
    buildPath: PUBLIC_PATH,
    customParts: [
      useVueLoader(),
      typescriptLoader(),
      setKendoAndJqueryToGlobal([
        /kendo.pdf/,
        /kendo.excel/
      ])
    ]
  };
  return [
    prod(conf),
    prodLegacy(conf),
    dev(conf)
  ];
};