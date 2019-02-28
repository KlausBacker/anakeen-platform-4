const path = require("path");

const {useVueLoader, typescriptLoader} = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "src/public");
module.exports = {
    moduleName: "vaultManager",
    libName: "AdminVaultManager",
    entry: {
      "vault-manager": [path.resolve(BASE_DIR, "src/vendor/Anakeen/AdminCenter/HubComponent/AdminCenterVaultManager/index.js")]
    },
    buildPath: PUBLIC_PATH,
    customParts: [
      useVueLoader(),
      typescriptLoader()
    ]
};