const path = require("path");
const { VueLoaderPlugin } = require("vue-loader");
const NodeExternals = require("webpack-node-externals");
const BASE_PATH = path.resolve(__dirname, "..");
const OUTPUT_PATH = {
  lib: path.resolve(BASE_PATH, "lib")
};

module.exports = config => ({
  output: {
    libraryTarget: "commonjs2",
    path: OUTPUT_PATH.lib
  },
  resolve: {
    extensions: [".ts", ".js"]
  },
  mode: "development",
  devtool: "inline-source-map",
  module: {
    rules: [
      { test: /\.vue$/, use: "vue-loader" },
      { test: /\.scss/, use: ["style-loader", "css-loader", "sass-loader"] },
      {
        test: /\.ts$/,
        loader: "ts-loader",
        options: {
          appendTsSuffixTo: [/\.vue$/],
          compilerOptions: {
            declaration: true,
            declarationDir: "./lib/types"
          }
        }
      }
    ]
  },
  externals: [NodeExternals({ importType: "commonjs" })],
  plugins: [new VueLoaderPlugin()]
});
