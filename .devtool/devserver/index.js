#!/usr/bin/env node

const webpack = require("webpack");
const rollup = require("rollup");
const webpackDevMiddleware = require("webpack-dev-middleware");
const express = require("express");
const app = express();
const proxy = require("express-http-proxy");
const config = require("./config.perso.js");
const ErrorOverlayPlugin = require("error-overlay-webpack-plugin");
const rollupConfig = config.getRollupConfig();
let firstRollup;

if (rollupConfig.length > 0) {
  firstRollup = new Promise((resolve, reject) => {
    config.getRollupConfig().forEach(currentElement => {
      const watcher = rollup.watch(currentElement.default);
      watcher.on("event", event => {
        if (event.code === "END") {
          setTimeout(resolve, 1000);
        }
      });
    });
  });
} else {
  firstRollup = Promise.resolve();
}

firstRollup.then(() => {
  config.getConfig().forEach(currentConfig => {
    if (currentConfig.mode !== "development") {
      return;
    }
    if (config.devtool) {
      currentConfig.devtool = config.devtool;
    }
    currentConfig.plugins = currentConfig.plugins || [];
    currentConfig.plugins.push(new ErrorOverlayPlugin());

    const compiler = webpack(currentConfig);

    const instance = webpackDevMiddleware(compiler, {
      publicPath: currentConfig.output.publicPath,
      writeToDisk: true
    });
    app.use(instance);
  });
});

app.use(
  "/",
  proxy(config.platformUrl, {
    proxyReqOptDecorator: function(proxyReqOpts) {
      if (config.credentials && config.credentials.user && config.credentials.password) {
        const buffer = new Buffer(`${config.credentials.user}:${config.credentials.password}`);
        proxyReqOpts.headers["Authorization"] = `Basic ${buffer.toString("base64")}`;
      }
      return proxyReqOpts;
    }
  })
);

app.listen(config.devServerPort, function(err) {
  if (err) {
    return console.error(err);
  }
  console.log(`Listening at http://localhost:${config.devServerPort}`);
});
