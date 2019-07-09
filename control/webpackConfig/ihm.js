const path = require("path");
const PACKAGE = require("../package.json");
const fs = require("fs");

const { prod } = require("@anakeen/webpack-conf");
const { scssLoader, cssLoader } = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "/src/public");

fs.writeFileSync(
  path.resolve(BASE_DIR, "src", "version.json"),
  JSON.stringify({ version: PACKAGE.version }),
  {"encoding": "utf-8"}
);

module.exports = () => {
  const conf = {
    moduleName: "control",
    entry: {
      control: [path.resolve(__dirname, "../src/src/Control/IHM/index.js")]
    },
    output : {
      publicPath: "./Anakeen/control/prod/"
    },
    buildPath: PUBLIC_PATH,
    customParts: [
      scssLoader({
        filename: "[name]-[chunkhash].css"
      }),
      cssLoader()
    ]
  };
  return prod(conf);
};
