const path = require('path');
const {prod, dev} = require("@anakeen/webpack-conf");
const { vueLoader,   addKendoGlobal,
  addDll } = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, '../');
const PUBLIC_PATH =  path.join(BASE_DIR, "src/public");
const USER_INTERFACES = path.resolve(
  BASE_DIR,
  "..",
  "node_modules",
  "@anakeen",
  "user-interfaces"
);

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
      addDll({
        context: BASE_DIR,
        manifest: path.join(
          USER_INTERFACES,
          "src",
          "public",
          "Anakeen",
          "assets",
          "legacy",
          "KendoUI-manifest.json"
        )
      }),
      addDll({
        context: BASE_DIR,
        manifest: path.join(
          USER_INTERFACES,
          "src",
          "public",
          "Anakeen",
          "ankDll",
          "legacy",
          "vueDll-manifest.json"
        )
      }),
      addDll({
        context: BASE_DIR,
        manifest: path.join(
          USER_INTERFACES,
          "src",
          "public",
          "Anakeen",
          "ankDll",
          "legacy",
          "ankKendoDll-manifest.json"
        )
      }),
      vueLoader(),
      addKendoGlobal([
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
