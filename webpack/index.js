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
            "hubAdmin": [path.resolve(BASE_DIR, "src/vendor/Anakeen/Hub/IHM/JS/hubAdmin.js")],
            "hubInstanciation": [path.resolve(BASE_DIR,"src/vendor/Anakeen/Hub/IHM/JS/hubInstanciation.js")],
            "hubConfiguration": [path.resolve(BASE_DIR,"src/vendor/Anakeen/Hub/SmartStructures/HubConfiguration/Render/HubConfiguration.js")],
            "hubInstanciationRender": [path.resolve(BASE_DIR,"src/vendor/Anakeen/Hub/SmartStructures/HubInstanciation/Render/HubInstanciation.js")]
        },
      buildPath: PUBLIC_PATH,
      customParts: [useVueLoader(),
        {
          module: {
            rules: [
              {
                test: /\.(ttf|eot|woff|woff2)$/,
                use: {
                  loader: "file-loader",
                  // options: {
                  //   name: "fonts/[name].[ext]",
                  //   publicPath: "./" // Take the directory into account
                  // }
                }
              },
            ]
          }
        }
      ]
    };
    return [
      prod(conf),
      prodLegacy(conf),
      dev(conf)
    ];
};