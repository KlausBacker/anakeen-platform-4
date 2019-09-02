const path = require("path");
const NodeExternals = require("webpack-node-externals");
const BASE_PATH = process.env.base
  ? process.env.base
  : path.resolve(__dirname, "..");
const OUTPUT_PATH = {
  lib: path.resolve(BASE_PATH, "lib")
};
const WebpackCopyPlugin = require("copy-webpack-plugin");

module.exports = () => ({
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
      {
        test: /\.ts$/,
        loader: "ts-loader",
        options: {
          appendTsSuffixTo: [/\.vue$/],
          compilerOptions: {
            declaration: true,
            declarationDir: path.resolve(__dirname, "../lib"),
            experimentalDecorators: true
            //declarationDir: "./lib/types"
          }
        }
      }
    ]
  },
  externals: [
    NodeExternals({
      importType: "commonjs",
      modulesDir: path.resolve(__dirname, "..", "..", "..", "node_modules")
    })
  ],
  plugins: [
    new WebpackCopyPlugin([{
      context: path.resolve(__dirname, "../types"),
      from: "**/*.d.ts",
      to: path.resolve(__dirname, "../lib/")
    }])
  ]
});
