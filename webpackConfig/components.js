const path = require('path');
const {prod, prodLegacy, dev} = require("@anakeen/webpack-conf");
const {useVueLoader} = require("@anakeen/webpack-conf/parts");
const VueLoaderPlugin = require('vue-loader/lib/plugin');


const BASE_DIR = path.resolve(__dirname, '../');
const PUBLIC_PATH = path.join(BASE_DIR, "anakeen-ui/src/public");

module.exports = () => {
  const conf = {
    moduleName: "ank-components",
    entry: {
      'ank-components': [path.resolve(__dirname, '../anakeen-ui/src/vendor/Anakeen/Components/main.js')],
    },
    buildPath: PUBLIC_PATH,
    customParts: [
      {
        resolve: {
          alias: {
            vue$: 'vue/dist/vue.esm.js',
          },
        },
        output: {
          library: 'ank-components',
          libraryTarget: "umd",
        },
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
      useVueLoader()
    ]
  };
  const confLegacy = {
    ...conf,
    entry: {
      'ank-components': ['@webcomponents/webcomponentsjs/webcomponents-bundle.js', path.resolve(__dirname, '../anakeen-ui/src/vendor/Anakeen/Components/main.js')],
    },
  };
  const confDev = {
    ...conf,
    entry: {
      'ank-components': [path.resolve(__dirname, '../anakeen-ui/src/vendor/Anakeen/Components/main-debug.js')],
    },
  };
  return [
    prod(conf),
    prodLegacy(confLegacy),
    dev(confDev)
  ];
};