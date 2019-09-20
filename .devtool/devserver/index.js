const webpack = require("webpack");
const webpackDevMiddleware = require("webpack-dev-middleware");
const express = require("express");
const app = express();
const proxy = require("express-http-proxy");
const config = require("./config.perso.js");
const merge = require("webpack-merge");

config.getDllConfig().forEach(currentElement => {
  currentElement.webpack.forEach(currentConfig => {
    if (currentConfig.mode !== "development") {
      return;
    }
    if (currentElement.context) {
      currentConfig.context = currentElement.context;
    }
    //Run a webpack watcher to reinit file when needed
    const compiler = webpack(currentConfig);
    compiler.watch({}, () => {
      console.log(`build of ${currentConfig.name} done`);
    });
    //If there is a path a add rule to express to handle the file
    if (currentElement.path) {
      app.get(currentElement.path.url, (req, res) => {
        res.sendFile(currentElement.path.local);
      });
    }
  });
});

config.getConfig().forEach(currentConfig => {
  if (currentConfig.mode !== "development") {
    return;
  }
  if (config.getDeps) {
    const alias = {
      alias: config.getDeps()
    };
    currentConfig.resolve = currentConfig.resolve || {};
    currentConfig.resolve = merge(currentConfig.resolve, alias);
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
