const path = require("path");

const { prod, dev } = require("@anakeen/webpack-conf");
const { vueLoader, addKendoGlobal, addJqueryGlobal } = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "/src/public");

module.exports = () => {
  const conf = {
    context: BASE_DIR,
    moduleName: "smartStructures",
    entry: {
      Dsearch: [
        path.resolve(
          __dirname,
          "../src/vendor/Anakeen/SmartStructures/Dsearch/Render/dsearch.js"
        )
      ],
      Mask: [
        path.resolve(
          __dirname,
          "../src/vendor/Anakeen/SmartStructures/Mask/Render/MaskView.js"
        )
      ],
      IuserGroup: [
        path.resolve(
          __dirname,
          "../src/vendor/Anakeen/SmartStructures/Iuser/Render/changeGroupView.js"
        )
      ],
      Iuser: [
        path.resolve(
          __dirname,
          "../src/vendor/Anakeen/SmartStructures/Iuser/Render/mainIuser.js"
        )
      ],
      TaskEdit: path.resolve(__dirname, "../src/vendor/Anakeen/SmartStructures/Task/Render/taskCrontab.js"),
      TaskCommon: path.resolve(__dirname, "../src/vendor/Anakeen/SmartStructures/Task/Render/taskExecute.js"),
      "search-view-grid-render": [
        path.resolve(
          __dirname,
          "../src/vendor/Anakeen/SmartStructures/Dsearch/Render/searchViewGrid.js"
        )
      ]
    },
    buildPath: PUBLIC_PATH,
    excludeBabel: [/node_modules\/style-loader/],
    customParts: [
      addKendoGlobal([/kendo.pdf/, /kendo.excel/], true),
      addJqueryGlobal(),
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
            )
          }
        }
      },
      vueLoader()
    ]
  };
  if (process.env.conf === "PROD") {
    return prod(conf);
  }
  if (process.env.conf === "DEV") {
    return dev(conf);
  }
  return [prod(conf), dev(conf)];
};
