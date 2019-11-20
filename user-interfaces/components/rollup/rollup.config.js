const path = require("path");
const Scss = require("rollup-plugin-scss");
const typescript = require("rollup-plugin-typescript2");
const VuePlugin = require("rollup-plugin-vue");
const magicImporter = require("node-sass-magic-importer");
const progress = require("rollup-plugin-progress");
const visualizer = require("rollup-plugin-visualizer");
const { string } = require("rollup-plugin-string");
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
export default {
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
    AnkSEList: path.resolve(BASE_PATH, "src/AnkSEList/index.ts"),
    AnkSmartElementTabs: path.resolve(BASE_PATH, "src/AnkSETabs/SmartElementTabs.ts"),
    AnkSmartElementTab: path.resolve(BASE_PATH, "src/AnkSETabs/SmartElementTab.ts"),
    AnkTab: path.resolve(BASE_PATH, "src/AnkSETabs/Tab.ts"),
    AnkSmartForm: path.resolve(BASE_PATH, "src/AnkSmartForm/index.ts"),
    AnkSmartElement: path.resolve(BASE_PATH, "src/AnkSmartElement/index.ts"),
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
    cleaner({
      targets: [OUTPUT_DIR]
    }),
    progress(),
    json(),
    Scss(),
    string({
      include: "**/*.template.kd"
    }),
    commonjs(),
    typescript({
      objectHashIgnoreUnknownHack: true,
      tsconfigDefaults: {
        sourceMap: true
      }
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
    }),
    visualizer({
      filename: path.resolve(OUTPUT_DIR, "stats/stats.html"),
      template: "treemap",
      title: "User interfaces NPM Components"
    })
  ]
};
