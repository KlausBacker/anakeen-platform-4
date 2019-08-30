const path = require("path");
const { useCache  } = require("./common");

const { prod, dev, legacy } = require("@anakeen/webpack-conf");
const webpack = require("webpack");
const {
  cssLoader,
  addFalseKendoGlobal,
  addDll,
  typeScriptLoader
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
      smartElementController: [
        path.resolve(
          __dirname,
          "../src/vendor/Anakeen/DOCUMENT/IHM/widgets/globalController/index.ts"
        )
      ]
    },
    buildPath: PUBLIC_PATH,
    excludeBabel: [/node_modules\/ckeditor/],
    customParts: [
      useCache,
      {
        resolve: {
          extensions: [".js"],
          alias: {
            "@anakeen/user-interfaces": BASE_DIR,
            dcpContextRoot: "",
            dcpDocument: path.resolve(
              BASE_DIR,
              "src/vendor/Anakeen/DOCUMENT/IHM/"
            ),
            dcpExternals: path.resolve(
              BASE_DIR,
              "src/public/uiAssets/externals/"
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
      typeScriptLoader({
        compilerOptions: {
          declaration: false
        }
      }),
      addDll({
        context: BASE_DIR,
        manifest: path.join(
          PUBLIC_PATH,
          "Anakeen",
          "assets",
          "legacy",
          "KendoUI-manifest.json"
        )
      }),
      addFalseKendoGlobal([/dcpExternals\/KendoUI\/KendoUI/]),
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
