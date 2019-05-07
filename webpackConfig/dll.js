const path = require("path");
const { useCache  } = require("./common");
const { deps } = require("@anakeen/webpack-conf");

const { addDll, addFalseKendoGlobal } = require("@anakeen/webpack-conf/parts");

const BASE_DIR = path.resolve(__dirname, "../");
const PUBLIC_PATH = path.join(BASE_DIR, "src/public");

module.exports = () => {
  const conf = {
    moduleName: "ankDll",
    entry: {
      ankKendoDll: [
        "@progress/kendo-ui/js/kendo.grid",
        "@progress/kendo-ui/js/kendo.toolbar",
        "@progress/kendo-ui/js/kendo.filtercell",
        "@progress/kendo-ui/js/kendo.splitter"
      ],
      vueDll: ["vue/dist/vue.esm.js", "axios"]
    },
    excludeBabel: [
      /node_modules\/axios/,
      /node_modules\/@progress\/.*/,
      /node_modules\/vue/
    ],
    buildPath: PUBLIC_PATH,
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
      addFalseKendoGlobal([/kendo.pdf/, /kendo.excel/])
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
