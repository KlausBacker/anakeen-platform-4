const fs = require("fs");
const path = require("path");
const gracefulFs = require("graceful-fs");

gracefulFs.gracefulify(fs);

const merge = require("webpack-merge");
const parts = require("./parts");

const commonConfig = ({ mode, moduleName, manifestPath }) => {
  return merge([
    {
      bail: true
    },
    parts.generateHashModuleName(),
    parts.checkDuplicatePackage(),
    parts.sourceMapLoader(),
    parts.clean(),
    parts.extractAssets({
      filename: `${mode}.json`,
      path: path.resolve(manifestPath, moduleName)
    })
  ]);
};

const generateLibWebpackConf = ({
  libName,
  vendorName = "Anakeen",
  mode = "prod",
  moduleName,
  manifestPath,
  entry,
  relativeOutputPath,
  buildPath,
  excludeBabel = false,
  customParts = []
}) => {
  const outputConfig = {
    output: {
      library: libName ? libName : "[name]",
      libraryTarget: "umd",
      libraryExport: "default"
    }
  };
  const libConfig = analyzeAndReturnWebpackConf({
    libName,
    vendorName,
    mode,
    moduleName,
    manifestPath,
    entry,
    relativeOutputPath,
    buildPath,
    excludeBabel,
    customParts
  });

  return merge(outputConfig, libConfig);
};

const analyzeAndReturnWebpackConf = ({
  vendorName = "Anakeen",
  mode = "prod",
  moduleName,
  manifestPath,
  entry,
  relativeOutputPath,
  buildPath,
  excludeBabel = [],
  withoutBabel = false,
  customParts = []
}) => {
  if (!relativeOutputPath) {
    relativeOutputPath = path.join(vendorName, moduleName, mode, "/");
  }
  if (!manifestPath) {
    manifestPath = path.join(buildPath, vendorName, "manifest");
  }
  const conf = {
    name: `${moduleName}_${mode}`,
    devtool: mode !== "dev" ? "sourcemap" : "cheap-module-eval-source-map",
    entry,
    output: {
      publicPath: path.join("/", relativeOutputPath),
      path: path.resolve(buildPath, relativeOutputPath),
      filename: "[name]-[hash].js",
      chunkFilename: "[name]-[hash].js"
    },
    mode: mode !== "dev" ? "production" : "development"
  };
  if (mode === "dev") {
    conf.output.filename = "[name].js";
  }

  const elements = [
    conf,
    commonConfig({
      mode,
      relativeOutputPath,
      buildPath,
      moduleName,
      manifestPath
    })
  ];

  if (!withoutBabel) {
    if (mode === "prod") {
      elements.push(parts.jsModernLoader(excludeBabel));
    }
    if (mode === "legacy") {
      elements.push(parts.jsLegacyLoader(excludeBabel));
    }
  }
  return merge([...elements, ...customParts]);
};

module.exports = {
  prod: ({
    vendorName,
    moduleName,
    manifestPath,
    entry,
    relativeOutputPath,
    buildPath,
    excludeBabel = [],
    customParts = [],
    withoutBabel = false
  }) => {
    return analyzeAndReturnWebpackConf({
      vendorName,
      moduleName,
      manifestPath,
      entry,
      relativeOutputPath,
      buildPath,
      excludeBabel,
      withoutBabel,
      customParts
    });
  },
  legacy: ({
    vendorName,
    moduleName,
    manifestPath,
    entry,
    relativeOutputPath,
    buildPath,
    excludeBabel = [],
    withoutBabel = false,
    customParts = []
  }) => {
    return analyzeAndReturnWebpackConf({
      vendorName,
      mode: "legacy",
      moduleName,
      manifestPath,
      entry,
      relativeOutputPath,
      buildPath,
      excludeBabel,
      withoutBabel,
      customParts
    });
  },
  dev: ({ vendorName, moduleName, manifestPath, entry, relativeOutputPath, buildPath, customParts = [] }) => {
    return analyzeAndReturnWebpackConf({
      vendorName,
      mode: "dev",
      moduleName,
      manifestPath,
      entry,
      relativeOutputPath,
      buildPath,
      customParts
    });
  },
  lib: ({
    vendorName,
    libName,
    mode = "prod",
    moduleName,
    manifestPath,
    entry,
    relativeOutputPath,
    buildPath,
    excludeBabel = [],
    withoutBabel = false,
    customParts = []
  }) => {
    return generateLibWebpackConf({
      mode,
      vendorName,
      libName,
      moduleName,
      manifestPath,
      entry,
      relativeOutputPath,
      buildPath,
      excludeBabel,
      withoutBabel,
      customParts
    });
  }
};
