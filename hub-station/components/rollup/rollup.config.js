const path = require("path");
const Scss = require("rollup-plugin-scss");
const typescript = require("rollup-plugin-typescript2");
const VuePlugin = require("rollup-plugin-vue");
const magicImporter = require("node-sass-magic-importer");
const progress = require("rollup-plugin-progress");
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
const conf = {
  input: {
    AnkHubElement: path.resolve(BASE_PATH, "src/HubElement/index.ts"),
    AnkHubElementMixin: path.resolve(BASE_PATH, "src/HubElement/Mixins/HubElementMixin.ts"),
    AnkHubStation: path.resolve(BASE_PATH, "src/HubStation/index.ts"),
    AnkHubUtil: path.resolve(BASE_PATH, "src/utils/HubEntriesUtil.ts")
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

export default conf;
