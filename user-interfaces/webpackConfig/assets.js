const path = require("path");
const { prod, dev } = require("@anakeen/webpack-conf");
const CopyWebpackPlugin = require("copy-webpack-plugin");
const { clean } = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "/src/public");

module.exports = () => {
  const conf = {
    context: BASE_DIR,
    moduleName: "assets",
    entry: {
      KendoUI: [path.resolve(__dirname, "./kendo/kendo.js")]
    },
    buildPath: PUBLIC_PATH,
    withoutBabel: true,
    customParts: [
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
  
            {
              //Images : Copy SmartStructures/<SST>/Images/<IMG> to public/Images/<SST>/<IMG>
              context: path.resolve(
                __dirname,
                "../src/vendor/Anakeen/SmartStructures/"
              ),
              from: "*/Images/**",
              transformPath(targetPath, absolutePath) {
                return targetPath.replace('/Images','').replace('TargetImages','Images');
              },
              to: path.resolve(__dirname, "../src/public/TargetImages/")
            },

            {
              //Images : Copy Anakeen/Images/<IMG> to public/Images/<IMG>
              context: path.resolve(
                __dirname,
                "../src/vendor/Anakeen/Images/"
              ),
              from: "**",

              to: path.resolve(__dirname, "../src/public/Images/")
            },
            //datatables
            {
              from: "../node_modules/datatables.net-bs4/css",
              to: path.resolve(
                __dirname,
                "../src/public/uiAssets/externals/datatables/css/"
              )
            },
            //TraceKit
            {
              from: "../node_modules/tracekit/tracekit.js",
              to: path.resolve(
                __dirname,
                "../src/public/uiAssets/externals/traceKit/traceKit.js"
              )
            },
            //ckeditor
            {
              from: "../node_modules/ckeditor4/",
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

  return [prod(conf), dev({ ...conf })];
};
