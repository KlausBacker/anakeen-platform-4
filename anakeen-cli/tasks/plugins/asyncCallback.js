"use strict";

const through = require("through2");

module.exports = (asyncCallback, all = false) => {
  const files = [];
  return through.obj(
    function(file, encoding, next) {
      const plugin = this;
      try {
        if (file.isNull()) {
          // nothing to do
          return next(null, file);
        }
        if (all) {
          asyncCallback(file)
            .then(asyncFiles => {
              if (Array.isArray(asyncFiles)) {
                asyncFiles.forEach(currentFile => {
                  files.push(currentFile);
                  plugin.push(currentFile);
                });
                next();
              } else {
                files.push(asyncFiles);
                next(null, asyncFiles);
              }
            })
            .catch(err => {
              next(err);
            });
        } else {
          files.push(file);
          next(null, file);
        }
      } catch (e) {
        next(e);
      }
    },
    function(next) {
      if (all) {
        next();
        return;
      }
      const plugin = this;
      try {
        asyncCallback(files)
          .then(() => {
            plugin.emit("asyncCallbackDone", true);
            next();
          })
          .catch(err => {
            plugin.emit("error", err);
            next(err);
          });
      } catch (e) {
        next(e);
      }
    }
  );
};
