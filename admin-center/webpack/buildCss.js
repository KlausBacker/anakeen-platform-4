const path = require("path");

const { prod, dev } = require("@anakeen/webpack-conf");
const { scssLoader } = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "/src/public");

module.exports = () => {
  const conf = {
    context: BASE_DIR,
    moduleName: "adminCenter",
    entry: {
      adminCenter:path.resolve(BASE_DIR, "src/vendor/Anakeen/AdminCenter/IHM/main.scss")
    },
    withoutBabel: true,
    buildPath: PUBLIC_PATH
  };
  const confProd = {
    ...conf,
    customParts: [
      scssLoader({
        filename: "[name]-[chunkhash].css",
        minify: true,
        removeJS: true
      })
    ]
  };
  const confDev = {
    ...conf,
    customParts: [
      scssLoader({
        filename: "[name].css",
        removeJS: true
      })
    ]
  };
  return [prod(confProd), dev(confDev)];
};
