const path = require("path");
const webpack = require("webpack");
const HardSourceWebpackPlugin = require("hard-source-webpack-plugin");
const { lib } = require("@anakeen/webpack-conf");

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
  moduleName: "admin",
  libName: "adminGlobal",
  entry: {
    AdminAccountManager: [
      path.resolve(BASE_DIR, "src/vendor/Anakeen/AdminCenter/IHM/HubComponent/AdminCenterAccounts/index.js")
    ],
    AdminEnumManager: [
      path.resolve(BASE_DIR, "src/vendor/Anakeen/AdminCenter/IHM/HubComponent/AdminCenterEnum/index.js")
    ],
    Admini18nManager: [
      path.resolve(BASE_DIR, "src/vendor/Anakeen/AdminCenter/IHM/HubComponent/AdminCenterI18n/index.js")
    ],
    AdminMailManager: [
      path.resolve(BASE_DIR, "src/vendor/Anakeen/AdminCenter/IHM/HubComponent/AdminCenterMail/index.js")
    ],
    AdminParameterManager: [
      path.resolve(BASE_DIR, "src/vendor/Anakeen/AdminCenter/IHM/HubComponent/AdminCenterParameters/index.js")
    ],
    AdminTokenManager: [
      path.resolve(BASE_DIR, "src/vendor/Anakeen/AdminCenter/IHM/HubComponent/AuthenticationTokensHub/index.js")
    ],
    AdminTrashManager: [
      path.resolve(BASE_DIR, "src/vendor/Anakeen/AdminCenter/IHM/HubComponent/AdminCenterTrash/index.js")
    ],
    AdminVaultManager: [
      path.resolve(BASE_DIR, "src/vendor/Anakeen/AdminCenter/IHM/HubComponent/AdminCenterVaultManager/index.js")
    ],
    AdminWorkflowManager: [
      path.resolve(BASE_DIR, "src/vendor/Anakeen/AdminCenter/IHM/HubComponent/AdminCenterWorkflow/index.js")
    ],
    AdminStructureManager: [
      path.resolve(BASE_DIR, "src/vendor/Anakeen/AdminCenter/IHM/HubComponent/AdminCenterStructureManager/index.js")
    ]
  },
  buildPath: PUBLIC_PATH,
  excludeBabel: [
    /node_modules\/axios/,
    /node_modules\/@progress\/.*/,
    /node_modules\/css-loader/,
    /node_modules\/vue/,
    /node_modules\/jsoneditor/,
    /node_modules\/brace/
  ],
  customParts: [
    { plugins: [new HardSourceWebpackPlugin()] },
    vueLoader(),
    typeScriptLoader(),
    addKendoGlobal([/kendo.pdf/, /kendo.excel/], true),
    addJqueryGlobal(),
    addVueGlobal(),
    {
      resolve: {
        extensions: [".js", ".vue", ".json", ".ts", ".tsx"]
      }
    }
  ],
};

module.exports = () => {
  if (process.env.conf === "PROD") {
    return lib({ ...conf, ...{ mode: "prod" } });
  }
  if (process.env.conf === "DEV") {
    return lib({ ...conf, ...{ mode: "dev" } });
  }
  return [lib({ ...conf, ...{ mode: "prod" } }), lib({ ...conf, ...{ mode: "dev" } })];
};
