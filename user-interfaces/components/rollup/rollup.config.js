const path = require("path");
const Scss = require("rollup-plugin-scss");
const typescript = require("rollup-plugin-typescript2");
const VuePlugin = require("rollup-plugin-vue");
const magicImporter = require("node-sass-magic-importer");
const progress = require("rollup-plugin-progress");
const cleaner = require("rollup-plugin-cleaner");
const json = require("rollup-plugin-json");
const NodeExternals = require("@yelo/rollup-node-external");

// Use commonjs plugin for vuejs vue-runtime-helpers/dist/normalize-component.js
const commonjs = require("rollup-plugin-commonjs");

// eslint-disable-next-line no-undef
const BASE_PATH = path.resolve(__dirname, "../");
const OUTPUT_DIR = path.resolve(BASE_PATH, "lib");
const MONOREPO_PATH = path.resolve(BASE_PATH, "../..");

// rollup.config.js
const conf = {
  input: {
    AnkAuthent: path.resolve(BASE_PATH, "src/AnkAuthent/index.ts"),
    AnkController: path.resolve(BASE_PATH, "src/AnkController/index.ts"),
    AnkIdentity: path.resolve(BASE_PATH, "src/AnkIdentity/index.ts"),
    AnkLoading: path.resolve(BASE_PATH, "src/AnkLoading/index.ts"),
    AnkLogout: path.resolve(BASE_PATH, "src/AnkLogout/index.ts"),
    AnkSmartElementGrid: path.resolve(BASE_PATH, "src/AnkSEGrid/SmartGrid.ts"),
    AnkSmartElementGridPager: path.resolve(BASE_PATH, "src/AnkSEGrid/SmartGridExport.ts"),
    AnkSmartElementGridColumnsButton: path.resolve(BASE_PATH, "src/AnkSEGrid/SmartGridColumns.ts"),
    AnkSmartElementGridExportButton: path.resolve(BASE_PATH, "src/AnkSEGrid/SmartGridExport.ts"),
    AnkSmartElementGridExpandButton: path.resolve(BASE_PATH, "src/AnkSEGrid/SmartGridExpand.ts"),
    AnkSmartElementVueGrid: path.resolve(BASE_PATH, "src/AnkSEGridNew/SmartGrid.ts"),
    AnkSmartElementList: path.resolve(BASE_PATH, "src/AnkSEList/index.ts"),
    AnkTabs: path.resolve(BASE_PATH, "src/AnkTabs/Tabs.ts"),
    AnkSmartElementTab: path.resolve(BASE_PATH, "src/AnkTabs/SmartElementTab.ts"),
    AnkTab: path.resolve(BASE_PATH, "src/AnkTabs/Tab.ts"),
    AnkSmartForm: path.resolve(BASE_PATH, "src/AnkSmartForm/index.ts"),
    AnkSmartElement: path.resolve(BASE_PATH, "src/AnkSmartElement/index.ts"),
    AnkI18NMixin: path.resolve(BASE_PATH, "mixins/AnkVueComponentMixin/I18nMixin.ts"),
    setup: path.resolve(BASE_PATH, "src/setup.ts")
  },
  output: {
    dir: OUTPUT_DIR,
    entryFileNames: "[name].[format].js",
    name: "[name]",
    format: "esm",
    sourcemap: true
  },
  external: NodeExternals({
    importType: "commonjs",
    modulesDir: path.resolve(MONOREPO_PATH, "node_modules")
  }),
  plugins: [
    progress(),
    json(),
    Scss(),
    commonjs(),
    typescript({
      objectHashIgnoreUnknownHack: true,
      tsconfigDefaults: {
        sourceMap: true
      },
      //Set the current directory explicitely for the devserver
      cwd: path.resolve(BASE_PATH, "../"),
      //Set the current directory explicitely for the devserver
      include: [ `${path.resolve(BASE_PATH, "../")}/*.ts+(|x)`, `${path.resolve(BASE_PATH, "../")}/**/*.ts+(|x)` ],
      exclude: [`${path.resolve(BASE_PATH, "../")}/*.d.ts`, `${path.resolve(BASE_PATH, "../")}/**/*.d.ts` ]
    }),
    VuePlugin({
      css: true, // Dynamically inject css as a <style> tag
      compileTemplate: true, // Explicitly convert template to render function
      style: {
        preprocessOptions: {
          scss: {
            importer: magicImporter()
          }
        }
      }
    })
  ]
};

// eslint-disable-next-line no-undef
if (process.env.CLEAN) {
  conf.plugins.push(
    cleaner({
      targets: [OUTPUT_DIR]
    })
  );
}

module.exports.default = conf;
