const path = require("path");

const businessApp = require("./businessApp");
const { lib } = require("@anakeen/webpack-conf");

module.exports = () => {
  const modeDev = {
    mode: process.env.dev === "DEV" ? "dev" : "prod"
  };
  if (process.env.element === "BUSINESS_APP") {
    return lib({ ...businessApp, ...modeDev });
  }
  return [
    lib(businessApp),
    lib({ ...businessApp, ...{ mode: "dev" } })
  ];
};
