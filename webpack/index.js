const path = require('path');
const {prod, prodLegacy, dev} = require("@anakeen/webpack-conf");
const { useVueLoader } = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "src/public");

module.exports = () => {
    const conf = {
        "moduleName": "hub",
        "entry": {
            "hub" : [path.resolve(BASE_DIR, "src/vendor/Anakeen/Hub/IHM/JS/hub.js")],
            "hubAdmin": [path.resolve(BASE_DIR, "src/vendor/Anakeen/Hub/IHM/JS/hubAdmin.js")]
        },
      buildPath: PUBLIC_PATH,
      customParts: [useVueLoader()]
    };
    return [
      prod(conf),
      prodLegacy(conf),
      dev(conf)
    ];
};