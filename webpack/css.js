const path = require("path");
const merge = require("webpack-merge");
const { useCache } = require("./common.part");

const { scssLoader } = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "/src/public");
const RELATIVE_PATH = "/Anakeen/DevCenter";


module.exports = () => {
  const conf = {
    mode: "production",
    entry: {
      main: [path.resolve(BASE_DIR, "src/vendor/Anakeen/DevelopmentCenter/vue/main.scss")],
    },
    output: {
      publicPath: RELATIVE_PATH,
      path: path.join(PUBLIC_PATH, RELATIVE_PATH),
    }
  };
  const finalConf = merge(conf, useCache, scssLoader({
    filename: "[name].css",
    minify: true,
    removeJS: true
  }));
  
  return finalConf;
};
