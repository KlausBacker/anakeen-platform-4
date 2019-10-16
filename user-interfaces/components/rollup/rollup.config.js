const path = require("path");
const Scss = require("rollup-plugin-scss");
const typescript = require("rollup-plugin-typescript");
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
    ankComponents: path.resolve(BASE_PATH, "src/index.ts")
  },
  output: {
    dir: OUTPUT_DIR,
    entryFileNames: "[name].[format].js",
    name: "[name]",
    format: "esm"
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
    typescript(),
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
