const path = require("path");

const { prod, dev } = require("@anakeen/webpack-conf");
const { scssLoader } = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "/src/public");

module.exports = () => {
  const conf = {
    context: BASE_DIR,
    moduleName: "theme",
    entry: {
      bootstrap: [path.resolve(__dirname, "../components/scss/bootstrap.scss")],
      components: [path.resolve(__dirname, "../components/scss/components.scss")],
      kendo: [path.resolve(__dirname, "../components/scss/kendo.scss")],
      smartElement: [path.resolve(__dirname, "../components/scss/AnkSmartElement.scss")],
      login: [path.resolve(__dirname, "../components/scss/login.scss")],
      colors: [path.resolve(__dirname, "../components/scss/colors.scss")]
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
