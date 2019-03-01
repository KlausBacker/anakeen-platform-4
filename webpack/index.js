const tokens = require("./tokenManager");
const vaults = require("./vaultManager");
const accounts = require("./accountManager");
const parameters = require("./parametersManager");
const { lib } = require("@anakeen/webpack-conf");

module.exports = () => {
  return [
    lib(tokens),
    lib(vaults),
    lib(parameters),
    lib(accounts)
  ];
};

