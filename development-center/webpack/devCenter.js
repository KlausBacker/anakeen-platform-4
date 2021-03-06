const path = require("path");
const { dontParse } = require("./common.part");

const {
  vueLoader,
  typeScriptLoader,
  addKendoGlobal,
  addJqueryGlobal,
  addVueGlobal
} = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "src/public");

const conf = {
  context: BASE_DIR,
  moduleName: "DevCenter",
  libName: "DevCenter",
  entry: {
    "vendor-selector": [path.resolve(BASE_DIR, "src/vendor/Anakeen/DevelopmentCenter/vue/hubComponents/DevBreadcrumb/index.js")],
    "breadcrumb": [path.resolve(BASE_DIR, "src/vendor/Anakeen/DevelopmentCenter/vue/hubComponents/DevVendorSelector/index.js")],
    "refresh-data": [path.resolve(BASE_DIR, "src/vendor/Anakeen/DevelopmentCenter/vue/hubComponents/DevRefreshData/index.js")],
    "search-engine": [path.resolve(BASE_DIR, "src/vendor/Anakeen/DevelopmentCenter/vue/hubComponents/DevSearchEngine/index.js")],
    "smart-structures": [path.resolve(BASE_DIR, "src/vendor/Anakeen/DevelopmentCenter/vue/hubComponents/DevSmartStructures/index.js")],
    "user-interface": [path.resolve(BASE_DIR, "src/vendor/Anakeen/DevelopmentCenter/vue/hubComponents/DevUserInterface/index.js")],
    "security": [path.resolve(BASE_DIR, "src/vendor/Anakeen/DevelopmentCenter/vue/hubComponents/DevSecurity/index.js")],
    "smart-elements": [path.resolve(BASE_DIR, "src/vendor/Anakeen/DevelopmentCenter/vue/hubComponents/DevSmartElements/index.js")],
    "workflow": [path.resolve(BASE_DIR, "src/vendor/Anakeen/DevelopmentCenter/vue/hubComponents/DevWorkflow/index.js")],
    "routes": [path.resolve(BASE_DIR, "src/vendor/Anakeen/DevelopmentCenter/vue/hubComponents/DevRoutes/index.js")],
    "localization": [path.resolve(BASE_DIR, "src/vendor/Anakeen/DevelopmentCenter/vue/hubComponents/DevLocalization/index.js")],
    "enumerates": [path.resolve(BASE_DIR, "src/vendor/Anakeen/DevelopmentCenter/vue/hubComponents/DevEnumerates/index.js")],
    "hub-instanciation": [path.resolve(BASE_DIR, "src/vendor/Anakeen/DevelopmentCenter/vue/hubComponents/DevHubInstanciation/index.js")],
  },
  buildPath: PUBLIC_PATH,
  excludeBabel: dontParse,
  customParts: [
    vueLoader(),
    typeScriptLoader(),
    addKendoGlobal([/kendo.pdf/, /kendo.excel/], true),
    addJqueryGlobal(),
    addVueGlobal(),
    {
      resolve: {
        extensions: [".js", ".vue", ".json", ".ts", ".tsx"]
      }
    },
    {
      resolve: {
        alias: {
          "devComponents": path.resolve(BASE_DIR,  "src/vendor/Anakeen/DevelopmentCenter/vue/components")
        }
      }
    }
  ]
};

conf.customParts = [...conf.customParts];

module.exports = conf;