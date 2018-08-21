const path = require('path');
const {deps} = require("@anakeen/webpack-conf");
const CopyWebpackPlugin = require('copy-webpack-plugin');
const { clean } = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, '../');
const PUBLIC_PATH =  path.join(BASE_DIR, "anakeen-ui/src/public");

module.exports = () => {
  const conf = {
    moduleName: "assets",
    entry: {
      'KendoUI': [path.resolve(__dirname, './kendo.js')]
    },
    buildPath: PUBLIC_PATH,
    customParts :[
      {
        output: {
          libraryTarget: "umd",
        },
        externals: {
          jquery: 'jQuery'
        }
      },
      clean(path.resolve(__dirname, '../anakeen-ui/src/public/uiAssets/externals/')),
      clean(path.resolve(__dirname, '../anakeen-ui/src/public/uiAssets/anakeen/')),
      {plugins: [
          new CopyWebpackPlugin(
            [
              {
                //dynacase-report
                context: path.resolve(__dirname, '../anakeen-ui/src/Apps/DOCUMENT/IHM/'),
                from: 'dynacaseReport.js',
                to: path.resolve(__dirname, '../anakeen-ui/src/public/uiAssets/anakeen/'),
              },
              //datatables
              {
                from: './node_modules/datatables.net-bs4/css',
                to: path.resolve(__dirname, '../anakeen-ui/src/public/uiAssets/externals/datatables/css/')
              },
              //TraceKit
              {
                from: './node_modules/tracekit/tracekit.js',
                to: path.resolve(__dirname, '../anakeen-ui/src/public/uiAssets/externals/traceKit/traceKit.js')
              },
              //jQuery
              {
                context: './node_modules/jquery/dist/',
                from: '*',
                to: path.resolve(__dirname, '../anakeen-ui/src/public/uiAssets/externals/jquery/')
              }
            ]
          )
        ]}
        ]
  };
  return [
    deps(conf)
  ];
};