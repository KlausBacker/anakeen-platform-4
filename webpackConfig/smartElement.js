const path = require("path");
const { prod, dev, legacy } = require("@anakeen/webpack-conf");
const webpack = require("webpack");
const {
  cssLoader,
  setKendoAndJqueryToGlobal
} = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "/src/public");

module.exports = () => {
  const conf = {
    moduleName: "smartElement",
    entry: {
      smartElement: [
        path.resolve(
          __dirname,
          "../src/vendor/Anakeen/DOCUMENT/IHM/mainDocument.js"
        )
      ],
      smartElementWidget: [
        path.resolve(
          __dirname,
          "../src/vendor/Anakeen/DOCUMENT/IHM/smartElement.js"
        )
      ]
    },
    buildPath: PUBLIC_PATH,
    excludeBabel: [/node_modules\/ckeditor/],
    customParts: [
      {
        resolve: {
          extensions: [".js"],
          alias: {
            dcpContextRoot: "",
            dcpDocument: path.resolve(
              __dirname,
              "../src/vendor/Anakeen/DOCUMENT/IHM/"
            ),
            dcpExternals: path.resolve(
              __dirname,
              "../src/public/uiAssets/externals/"
            ),
            datatables: "datatables.net",
            "datatables-bootstrap": "datatables.net-bs4",
            "kendo-culture-fr":
              "@progress/kendo-ui/js/cultures/kendo.culture.fr-FR",
            tooltip: "bootstrap/js/src/tooltip",
            documentCkEditor: path.resolve(__dirname, "./ckeditor/ckeditor.js")
          }
        }
      },
      {
        plugins: [
          new webpack.ProvidePlugin({
            Popper: ["popper.js", "default"]
          })
        ]
      },
      setKendoAndJqueryToGlobal([/dcpExternals\/KendoUI\/KendoUI/]),
      cssLoader()
    ]
  };
  if (process.env.conf === "PROD") {
    return prod(conf);
  }
  if (process.env.conf === "DEV") {
    return dev(conf);
  }
  if (process.env.conf === "LEGACY") {
    return legacy(conf);
  }
  return [prod(conf), dev(conf), legacy(conf)];
};
