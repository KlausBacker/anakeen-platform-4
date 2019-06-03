const devCenter = require("./devCenter.js");
const { lib } = require("@anakeen/webpack-conf");

module.exports = () => {
  const modeDev = {
    mode: process.env.dev === "DEV" ? "dev" : "prod"
  };
  if (process.env.element === "DEVCENTER") {
    return lib({ ...devCenter, ...modeDev });
  }
  
  return [
    lib(devCenter),
    lib({ ...devCenter, ...{ mode: "dev" } })
  ];
};
