const path = require('path');
const {prod, prodLegacy, dev} = require("@anakeen/webpack-conf");
const {useVueLoader, setKendoAndJqueryToGlobal} = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../admin-center/");
const PUBLIC_PATH = path.resolve(BASE_DIR, "src/public");

const adminPluginsEntries = require('./config/pluginsEntries');

module.exports = () => {
  const conf = {
    "moduleName": "adminCenter",
    "entry": {
      'adminCenter': [path.resolve(BASE_DIR, 'src/vendor/Anakeen/AdminCenter/Components/main.js')],
      ...adminPluginsEntries
    },
    buildPath: PUBLIC_PATH,
    customParts: [
      useVueLoader(),
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