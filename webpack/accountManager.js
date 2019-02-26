const path = require("path");

const {useVueLoader, typescriptLoader} = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "src/public");
module.exports = {
  moduleName: "accountManager",
  libName: "AdminAccountManager",
  entry: {
    "account-manager": [path.resolve(BASE_DIR, "src/vendor/Anakeen/AdminCenter/HubComponent/AdminCenterAccounts/index.js")]
  },
  buildPath: PUBLIC_PATH,
  customParts: [
    useVueLoader(),
    typescriptLoader(),
    {
      resolve: {
        extensions: [".js", ".vue", ".json", ".ts", ".tsx"]
      },
    }
  ]
};