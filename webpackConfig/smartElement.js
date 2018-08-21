const path = require('path');
const {prod, dev, prodLegacy} = require("@anakeen/webpack-conf");
const webpack = require('webpack');
const {cssLoader} = require("@anakeen/webpack-conf/parts");
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
      {
        externals: {
          'dcpExternals/KendoUI/KendoUI': 'kendo', // For require("kendo") in mainDocument.js
          './kendo.core': 'kendo',
          './kendo.autocomplete': 'jQuery.fn.kendoX',
          './kendo.binder': 'jQuery.fn.kendoX',
          './kendo.button': 'jQuery.fn.kendoX',
          './kendo.calendar': 'jQuery.fn.kendoX',
          './kendo.color': 'jQuery.fn.kendoX',
          './kendo.colorpicker': 'jQuery.fn.kendoX',
          './kendo.combobox': 'jQuery.fn.kendoX',
          './kendo.data': 'jQuery.fn.kendoX',
          './kendo.data.odata': 'jQuery.fn.kendoX',
          './kendo.data.xml': 'jQuery.fn.kendoX',
          './kendo.dateinput': 'jQuery.fn.kendoX',
          './kendo.datepicker': 'jQuery.fn.kendoX',
          './kendo.datetimepicker': 'jQuery.fn.kendoX',
          './kendo.draganddrop': 'jQuery.fn.kendoX',
          './kendo.dropdownlist': 'jQuery.fn.kendoX',
          './kendo.editable': 'jQuery.fn.kendoX',
          './kendo.fx': 'jQuery.fn.kendoX',
          './kendo.list': 'jQuery.fn.kendoX',
          './kendo.listview': 'jQuery.fn.kendoX',
          './kendo.menu': 'jQuery.fn.kendoX',
          './kendo.mobile.scroller': 'jQuery.fn.kendoX',
          './kendo.multiselect': 'jQuery.fn.kendoX',
          './kendo.notification': 'jQuery.fn.kendoX',
          './kendo.numerictextbox': 'jQuery.fn.kendoX',
          './kendo.pager': 'jQuery.fn.kendoX',
          './kendo.popup': 'jQuery.fn.kendoX',
          './kendo.resizable': 'jQuery.fn.kendoX',
          './kendo.selectable': 'jQuery.fn.kendoX',
          './kendo.slider': 'jQuery.fn.kendoX',
          './kendo.splitter': 'jQuery.fn.kendoX',
          './kendo.tabstrip': 'jQuery.fn.kendoX',
          './kendo.timepicker': 'jQuery.fn.kendoX',
          './kendo.userevents': 'jQuery.fn.kendoX',
          './kendo.validator': 'jQuery.fn.kendoX',
          './kendo.virtuallist': 'jQuery.fn.kendoX',
          './kendo.window': 'jQuery.fn.kendoX',

          jquery: 'jQuery',
        }
      },
      cssLoader(),
    ]
  };
  return [
    prod(conf),
    prodLegacy(conf),
    dev(conf)
  ];
};