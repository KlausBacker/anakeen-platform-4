"use strict";

const through = require("through2");

module.exports = end => {
  return through.obj(
    (file, encoding, callback) => {
      callback(null, file);
    },
    function(callback) {
      this.emit("end");
      callback();
    }
  );
};
