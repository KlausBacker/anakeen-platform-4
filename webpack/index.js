const tokens = require("./tokenManager");
const vaults = require("./vaultManager");
const accounts = require("./accountManager");
const i18n = require("./i18nManager");
const parameters = require("./parametersManager");
const { lib } = require("@anakeen/webpack-conf");

module.exports = () => {
  const modeDev = {
    mode: process.env.dev === "DEV" ? "dev" : "prod"
  };
  if (process.env.element === "TOKENS") {
    return lib({ ...tokens, ...modeDev });
  }
  if (process.env.element === "VAULTFS") {
    return lib({ ...vaults, ...modeDev });
  }
  if (process.env.element === "PARAMETERS") {
    return lib({ ...parameters, ...modeDev });
  }
  if (process.env.element === "ACCOUNTS") {
    return lib({ ...accounts, ...modeDev });
  }
  if (process.env.element === "I18N") {
    return lib({ ...i18n, ...modeDev });
  }
  return [
    lib(tokens),
    lib({ ...tokens, ...{ mode: "dev" } }),
    lib(vaults),
    lib({ ...vaults, ...{ mode: "dev" } }),
    lib(parameters),
    lib({ ...parameters, ...{ mode: "dev" } }),
    lib(accounts),
    lib({ ...accounts, ...{ mode: "dev" } }),
    lib(i18n),
    lib({ ...i18n, ...{ mode: "dev" } })
  ];
};
