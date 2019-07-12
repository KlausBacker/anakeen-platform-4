const webpack = require("webpack");
const webpackDevMiddleware = require("webpack-dev-middleware");
const webpackHotMiddleware = require("webpack-hot-middleware");
const express = require("express");
const app = express();
const proxy = require("express-http-proxy");
const config = require("./config.perso.js");
const merge = require("webpack-merge");
//const hotMiddlewareScript = 'webpack-hot-middleware/client?path=/__webpack_hmr&timeout=20000&reload=true';

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
  //Add HMR entry
  /*currentConfig.entry = Object.keys(currentConfig.entry).reduce(
    (acc, currentEntry) => {
      const entry = currentConfig.entry[currentEntry];
      entry.push(hotMiddlewareScript);
      acc[currentEntry] = entry;
      return acc;
    },
    {}
  );*/
  //Add HMR plugin
  /*currentConfig.plugins = currentConfig.plugins || [];
  currentConfig.plugins.push(new webpack.HotModuleReplacementPlugin());
  currentConfig.plugins.push(new webpack.NoEmitOnErrorsPlugin());*/

  const compiler = webpack(currentConfig);

  const instance = webpackDevMiddleware(compiler, {
    publicPath: currentConfig.output.publicPath
  });
  app.use(instance);

  //const wphmw = webpackHotMiddleware(compiler);
  //app.use(wphmw);
});

app.use(
  "/",
  proxy(config.platformUrl, {
    proxyReqOptDecorator: function(proxyReqOpts) {
      if (
        config.credentials &&
        config.credentials.user &&
        config.credentials.password
      ) {
        const buffer = new Buffer(
          `${config.credentials.user}:${config.credentials.password}`
        );
        proxyReqOpts.headers["Authorization"] = `Basic ${buffer.toString(
          "base64"
        )}`;
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
