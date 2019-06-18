const path = require("path");
const { dontParse, dllPart, useCache  } = require("./common.part");

const {
  vueLoader,
  addFalseKendoGlobal
} = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "src/public");

const conf = {
  moduleName: "DevCenter",
  entry: {
    "vendor-selector": [path.resolve(BASE_DIR, "src/vendor/Anakeen/DevelopmentCenter/vue/hubComponents/DevBreadcrumb/index.js")],
    "breadcrumb": [path.resolve(BASE_DIR, "src/vendor/Anakeen/DevelopmentCenter/vue/hubComponents/DevVendorSelector/index.js")],
    "refresh-data": [path.resolve(BASE_DIR, "src/vendor/Anakeen/DevelopmentCenter/vue/hubComponents/DevRefreshData/index.js")],
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
    useCache,
    vueLoader(),
    addFalseKendoGlobal([/kendo.pdf/, /kendo.excel/]),
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

conf.customParts = [...conf.customParts, ...dllPart];

module.exports = conf;