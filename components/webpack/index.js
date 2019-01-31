const merge = require("webpack-merge");

const baseConfig = require("./base");
const prodConfig = require("./prod");
const devConfig = require("./dev");
const commonConfig = require("./common");
const umdConfig = require("./umd");

module.exports = env => {
  console.log(env);
  if (env === "production") {
    return [
      merge(baseConfig, prodConfig, commonConfig),
      merge(baseConfig, prodConfig, umdConfig)
    ]
  }
  return [
    merge(baseConfig, devConfig, commonConfig),
    merge(baseConfig, devConfig, umdConfig)
  ];
};