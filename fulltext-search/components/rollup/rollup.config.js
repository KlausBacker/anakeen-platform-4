const path = require("path");
const Scss = require("rollup-plugin-postcss");
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
    AnkFulltextSmartCriteria: path.resolve(BASE_PATH, "src/AnkFulltextSmartCriteria/index.ts"),
    AnkFulltextSmartElementGrid: path.resolve(BASE_PATH, "src/AnkFulltextSmartElementGrid/index.ts")
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
