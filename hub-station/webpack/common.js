const HardSourceWebpackPlugin = require("hard-source-webpack-plugin");

module.exports.useCache = {
  plugins: [new HardSourceWebpackPlugin()]
};
