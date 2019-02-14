const path = require("path");

const BASE_PATH = path.resolve(__dirname, "..");
const ENTRIES_PATH = {
  lib: path.resolve(BASE_PATH, "src/index.ts")
};

module.exports = {
  entry: {
    "hub-components.common": ENTRIES_PATH.lib
  },
  output: {
    libraryTarget: "commonjs"
  }
};
