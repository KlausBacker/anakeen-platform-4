const path = require("path");
const HardSourceWebpackPlugin = require("hard-source-webpack-plugin");

const BASE_DIR = path.resolve(__dirname, "../");


module.exports.dontParse = [
  /node_modules\/axios/,
  /node_modules\/@progress\/.*/,
  /node_modules\/css-loader/,
  /node_modules\/vue/,
  /node_modules\/jsoneditor/,
  /node_modules\/brace/
];


module.exports.useCache = {
  plugins: [new HardSourceWebpackPlugin()]
};

