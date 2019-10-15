const tokens = require("./tokenManager");
const vaults = require("./vaultManager");
const accounts = require("./accountManager");
const i18n = require("./i18nManager");
const parameters = require("./parametersManager");
const mail = require("./mailManager");
const workflow = require("./workflowManager");
const trash = require("./trashManager");
const { lib } = require("@anakeen/webpack-conf");

module.exports = (entry) => {
  const modeDev = {
    mode: process.env.dev === "DEV" || !!entry ? "dev" : "prod"
  };
  const type = entry || process.env.element;
  if (type === "TOKENS") {
    return lib({ ...tokens, ...modeDev });
  }
  if (type === "VAULTFS") {
    return lib({ ...vaults, ...modeDev });
  }
  if (type === "PARAMETERS") {
    return lib({ ...parameters, ...modeDev });
  }
  if (type === "ACCOUNTS") {
    return lib({ ...accounts, ...modeDev });
  }
  if (type === "I18N") {
    return lib({ ...i18n, ...modeDev });
  }
  if (type === "MAIL") {
    return lib({ ...mail, ...modeDev });
  }
  if (type === "WORKFLOW") {
    return lib({ ...workflow, ...modeDev });
  }
  if (type === "TRASH") {
    return lib({ ...trash, ...modeDev });
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
    lib({ ...i18n, ...{ mode: "dev" } }),
    lib(mail),
    lib({ ...mail, ...{ mode: "dev" } }),
    lib(workflow),
    lib({ ...workflow, ...{ mode: "dev"} }),
    lib(trash),
    lib({ ...trash, ...{ mode: "dev"} })
  ];
};
