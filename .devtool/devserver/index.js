const webpack = require("webpack");
const rollup = require("rollup");
const webpackDevMiddleware = require("webpack-dev-middleware");
const express = require("express");
const app = express();
const proxy = require("express-http-proxy");
const config = require("./config.perso.js");
const merge = require("webpack-merge");

config.getRollupConfig().forEach(currentElement => {
  console.log(currentElement);
  const compiler = rollup.watch(currentElement.default);
  compiler.on('event', event => {
    console.log("ROLLUP ", event);
  });
});

config.getConfig().forEach(currentConfig => {
  if (currentConfig.mode !== "development") {
    return;
  }
  if (config.devtool) {
    currentConfig.devtool = config.devtool;
  }
  currentConfig.plugins = currentConfig.plugins || [];

  const compiler = webpack(currentConfig);

  const instance = webpackDevMiddleware(compiler, {
    publicPath: currentConfig.output.publicPath
  });
  app.use(instance);
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
