"use strict";

const through = require("through2");
const PluginError = require("plugin-error");

module.exports = asyncCallback => {
  return through.obj(function(file, encoding, callback) {
    const plugin = this;
    if (file.isNull()) {
      // nothing to do
      return callback(null, file);
    }
    try {
      asyncCallback(file)
        .then(() => {
          return callback(null, file);
        })
        .catch(err => {
          plugin.emit("error", new PluginError(asyncCallback, err));
          callback(err);
        });
    } catch (e) {
      callback(e);
    }
  });
};
