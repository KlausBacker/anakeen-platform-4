const path = require('path');
const {prod, prodLegacy, dev} = require("@anakeen/webpack-conf");
const {cssLoader} = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, '../');
const PUBLIC_PATH = path.join(BASE_DIR, "anakeen-ui/src/public");

module.exports = () => {
  const conf = {
    moduleName: "smartStructures",
    entry: {
      Dsearch: [path.resolve(__dirname, '../anakeen-ui/src/vendor/Anakeen/SmartStructures/Dsearch/Render/dsearch.js')],
      Helppage: [path.resolve(__dirname, '../anakeen-ui/src/vendor/Anakeen/SmartStructures/Helppage/Render/helppage.js')],
      Mask: [path.resolve(__dirname, '../anakeen-ui/src/vendor/Anakeen/SmartStructures/Mask/Render/MaskView.js')],
      IuserGroup: [path.resolve(__dirname, '../anakeen-ui/src/vendor/Anakeen/SmartStructures/Iuser/Render/changeGroupView.js')],
      Iuser: [path.resolve(__dirname, '../anakeen-ui/src/vendor/Anakeen/SmartStructures/Iuser/Render/mainIuser.js')]
    },
    buildPath: PUBLIC_PATH,
    customParts: [
      {
        resolve: {
          extensions: ['.js'],
          alias: {
            dcpContextRoot: '',
            dcpDocument: path.resolve(__dirname, '../anakeen-ui/src/Apps/DOCUMENT/IHM/'),
            dcpExternals: path.resolve(__dirname, '../anakeen-ui/src/public/uiAssets/externals/'),
          },
        },
      },
      {
        externals: {
          jquery: 'jQuery'
        }
      },
      cssLoader()
    ]
  };
  return [
    prodLegacy(conf),
    dev(conf)
  ];
};