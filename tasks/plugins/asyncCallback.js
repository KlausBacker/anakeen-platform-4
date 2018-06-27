"use strict";

const through = require("through2");
const PluginError = require("plugin-error");

module.exports = asyncCallback => {
  const files = [];
  return through.obj(
    (file, encoding, callback) => {
      if (file.isNull()) {
        // nothing to do
        return callback(null, file);
      }
      files.push(file);
      callback(null, file);
    },
    function(callback) {
      const plugin = this;
      try {
        asyncCallback(files)
          .then(() => {
            plugin.emit("asyncCallbackDone", true);
            callback();
          })
          .catch(err => {
            plugin.emit("error", new PluginError(asyncCallback, err));
            callback(err);
          });
      } catch (e) {
        callback(e);
      }
    }
  );
};
