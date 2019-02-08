const path = require("path");
const { VueLoaderPlugin } = require("vue-loader");

const BASE_PATH = path.resolve(__dirname, "..");
const OUTPUT_PATH = {
  lib: path.resolve(BASE_PATH, "lib")
};

module.exports = {
  output: {
    path: OUTPUT_PATH.lib
  },
  resolve: {
    extensions: [".ts", ".js"]
  },
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
  externals: [
    // include only relative assets
    function(context, request, callback) {
      if (!request.match(/(?:^|!)(?:\.|\.\.)?\//))
        return callback(null, `commonjs ${request}`);
      callback();
    }
  ],
  plugins: [
    new VueLoaderPlugin(),
  ]
};
