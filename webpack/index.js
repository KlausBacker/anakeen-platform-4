const path = require('path');

const BASE_DIR = path.resolve(__dirname, "../");
const tokens = require("./tokenManager");
const vaults = require("./vaultManager");
const accounts = require("./accountManager");
const parameters = require("./parametersManager");
const { lib } = require("@anakeen/webpack-conf");

module.exports = () => {
  if (process.env.element === "TOKENS") {
    return lib(tokens);
  }
  if (process.env.element === "VAULTFS") {
    return lib(vaults);
  }
  if (process.env.element === "PARAMETERS") {
    return lib(parameters);
  }
  if (process.env.element === "ACCOUNTS") {
    return lib(accounts);
  }
  return [
    lib(tokens),
    lib(vaults),
    lib(parameters),
    lib(accounts)
  ];
};

