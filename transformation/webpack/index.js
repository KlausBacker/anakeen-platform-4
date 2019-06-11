const tedock = require("./teDockManager");
const { lib } = require("@anakeen/webpack-conf");

module.exports = () => {
  const modeDev = {
    mode: process.env.dev === "DEV" ? "dev" : "prod"
  };
  if (process.env.element === "TEDOCK") {
    return lib({ ...tedock, ...modeDev });
  }
  return [
    lib(tedock),
    lib({ ...tedock, ...{ mode: "dev" } }),
  ];
};
