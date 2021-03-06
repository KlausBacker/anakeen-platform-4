const { CleanWebpackPlugin } = require("clean-webpack-plugin");
const AssetsWebpackPlugin = require("assets-webpack-plugin");
const DuplicatePackageCheckerPlugin = require("duplicate-package-checker-webpack-plugin");
const HardSourceWebpackPlugin = require("hard-source-webpack-plugin");
const webpack = require("webpack");

/**
 * Add an alert if some package are duplicated
 *
 * @returns {{plugins: DuplicatePackageCheckerPlugin[]}}
 */
exports.checkDuplicatePackage = () => ({
  plugins: [new DuplicatePackageCheckerPlugin()]
});

exports.addCache = () => {
  if (process.env.NO_CACHE) {
    return [];
  }
  return {
    plugins: [new HardSourceWebpackPlugin()]
  }
};

/**
 * Remove generated files of path
 *
 * @returns {{plugins: CleanWebpackPlugin[]}}
 */
exports.clean = () => ({
  plugins: [
    new CleanWebpackPlugin({
      cleanOnceBeforeBuildPatterns: ["**/*", "!*-manifest.json"]
    })
  ]
});

/**
 * Generate json files that describe builded assets
 *
 * @param filename
 * @param path
 * @returns {{plugins: AssetsWebpackPlugin[]}}
 */
exports.extractAssets = ({ filename, path }) => ({
  plugins: [
    new AssetsWebpackPlugin({
      filename,
      path
    })
  ]
});

/**
 * Generate hash for all modules
 * @returns {{plugins: (webpack.HashedModuleIdsPlugin|HashedModuleIdsPlugin)[]}}
 */
exports.generateHashModuleName = () => ({
  plugins: [
    new webpack.HashedModuleIdsPlugin({
      hashFunction: "sha256",
      hashDigest: "hex",
      hashDigestLength: 20
    })
  ]
});

/**
 * Add name to chunks
 * @returns {{plugins: *[]}}
 */
exports.generateNamedChunk = () => ({
  plugins: [
    new webpack.NamedModulesPlugin(),
    new webpack.NamedChunksPlugin(),
    {
      apply(compiler) {
        compiler.plugin("compilation", compilation => {
          compilation.plugin("before-module-ids", modules => {
            modules.forEach(module => {
              if (module.id !== null) {
                return;
              }
              module.id = module.identifier();
            });
          });
        });
      }
    }
  ]
});
