const path = require("path");
const { useCache } = require("./common");

const { prod, dev, legacy } = require("@anakeen/webpack-conf");
const { vueLoader, addDll } = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "/src/public");

module.exports = () => {
  const conf = {
    moduleName: "smartStructures",
    entry: {
      Dsearch: [
        path.resolve(
          __dirname,
          "../src/vendor/Anakeen/SmartStructures/Dsearch/Render/dsearch.js"
        )
      ],
      Helppage: [
        path.resolve(
          __dirname,
          "../src/vendor/Anakeen/SmartStructures/Helppage/Render/helppage.js"
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
      useCache,
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
      addDll({
        context: BASE_DIR,
        manifest: path.join(
          PUBLIC_PATH,
          "Anakeen",
          "ankDll",
          "legacy",
          "ankKendoDll-manifest.json"
        )
      }),
      addDll({
        context: BASE_DIR,
        manifest: path.join(
          PUBLIC_PATH,
          "Anakeen",
          "ankDll",
          "legacy",
          "vueDll-manifest.json"
        )
      }),
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
  if (process.env.conf === "LEGACY") {
    return legacy(conf);
  }
  return [prod(conf), dev(conf), legacy(conf)];
};
