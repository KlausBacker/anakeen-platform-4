const path = require('path');
const {prod, dev, prodLegacy} = require("@anakeen/webpack-conf");
const webpack = require('webpack');
const {cssLoader, setKendoAndJqueryToGlobal} = require("@anakeen/webpack-conf/parts");
const HtmlWebpackPlugin = require('html-webpack-plugin');
const HtmlWebpackInlineSVGPlugin = require('html-webpack-inline-svg-plugin');

const BASE_DIR = path.resolve(__dirname, '../');
const PUBLIC_PATH = path.join(BASE_DIR, "anakeen-ui/src/public");

module.exports = () => {
  const conf = {
    moduleName: "smartElement",
    entry: {
      'smartElement': [path.resolve(__dirname, '../anakeen-ui/src/Apps/DOCUMENT/IHM/mainDocument.js')],
      'smartElementWidget': [path.resolve(__dirname, '../anakeen-ui/src/Apps/DOCUMENT/IHM/smartElement.js')]
    },
    buildPath: PUBLIC_PATH,
    excludeBabel: /node_modules\/ckeditor/,
    customParts: [
      {
        resolve: {
          extensions: ['.js'],
          alias: {
            dcpContextRoot: '',
            dcpDocument: path.resolve(__dirname, '../anakeen-ui/src/Apps/DOCUMENT/IHM/'),
            dcpExternals: path.resolve(__dirname, '../anakeen-ui/src/public/uiAssets/externals/'),
            datatables: 'datatables.net',
            'datatables-bootstrap': 'datatables.net-bs4',
            'kendo-culture-fr': '@progress/kendo-ui/js/cultures/kendo.culture.fr-FR',
            tooltip: 'bootstrap/js/src/tooltip',
            documentCkEditor: path.resolve(__dirname, './ckeditor.js'),
          },
        },
      },
      {
        plugins: [
          new webpack.ProvidePlugin({
            Popper: ['popper.js', 'default'],
          }),
          new HtmlWebpackPlugin({
            filename: path.resolve(__dirname, '../anakeen-ui/src/vendor/Anakeen/Routes/Ui/Templates/document-view.html.mustache'),
            template: path.resolve(__dirname, '../anakeen-ui/src/Apps/DOCUMENT/IHM/document-view.html.mustache'),
            inject: false
          }),
          new HtmlWebpackInlineSVGPlugin(),
        ],
      },
      setKendoAndJqueryToGlobal([
        /dcpExternals\/KendoUI\/KendoUI/
      ]),
      cssLoader(),
    ]
  };
  return [
    prod(conf),
    prodLegacy(conf),
    dev(conf)
  ];
};