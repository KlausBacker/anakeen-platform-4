const path = require("path");
const { useCache } = require("./common");
const { deps } = require("@anakeen/webpack-conf");
const CopyWebpackPlugin = require("copy-webpack-plugin");
const { clean } = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "/src/public");

module.exports = () => {
  const conf = {
    moduleName: "assets",
    entry: {
      KendoUI: [path.resolve(__dirname, "./kendo/kendo.js")]
    },
    buildPath: PUBLIC_PATH,
    withoutBabel: true,
    customParts: [
      useCache,
      {
        module: {
          rules: [
            {
              test: require.resolve("jquery"),
              use: [
                {
                  loader: "expose-loader",
                  options: "jQuery"
                },
                {
                  loader: "expose-loader",
                  options: "$"
                }
              ]
            }
          ]
        }
      },
      clean(path.resolve(__dirname, "../src/public/uiAssets/externals/")),
      clean(path.resolve(__dirname, "../src/public/uiAssets/anakeen/")),
      {
        plugins: [
          new CopyWebpackPlugin([
            {
              //smart element report
              context: path.resolve(
                __dirname,
                "../src/vendor/Anakeen/DOCUMENT/IHM/"
              ),
              from: "dynacaseReport.js",
              to: path.resolve(__dirname, "../src/public/uiAssets/anakeen/")
            },
            //datatables
            {
              from: "./node_modules/datatables.net-bs4/css",
              to: path.resolve(
                __dirname,
                "../src/public/uiAssets/externals/datatables/css/"
              )
            },
            //TraceKit
            {
              from: "./node_modules/tracekit/tracekit.js",
              to: path.resolve(
                __dirname,
                "../src/public/uiAssets/externals/traceKit/traceKit.js"
              )
            },
            //ckeditor
            {
              from: "./node_modules/ckeditor/",
              to: path.resolve(
                __dirname,
                "../src/public/uiAssets/externals/ckeditor"
              )
            }
          ])
        ]
      }
    ]
  };
  if (process.env.conf === "DEV") {
    return deps({ ...conf, ...{ mode: "dev" } });
  }
  if (process.env.conf === "LEGACY") {
    return deps(conf);
  }

  return [deps(conf), deps({ ...conf, ...{ mode: "dev" } })];
};
