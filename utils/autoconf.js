const fs = require("fs");
const xml2js = require("xml2js");

const CONF_NAME = "./.anakeen-cli.xml";

exports.autoconf = () => {
  return new Promise((resolve, reject) => {
    //read file
    fs.readFile(CONF_NAME, { encoding: "utf-8" }, (err, content) => {
      if (err) {
        reject(err);
      }
      //read xml
      xml2js.parseString(
        content,
        { tagNameProcessors: [xml2js.processors.stripPrefix] },
        (err, data) => {
          if (err) {
            reject(err);
          }
          //convert xml data to arguments
          const conf = {};
          if (data.config.path) {
            const path = data.config.path[0];
            if (path.sourcePath) {
              conf.sourcePath = path.sourcePath[0];
            }
            if (path.targetPath) {
              conf.targetPath = path.targetPath[0];
            }
          }
          if (data.config.deploy) {
            const deploy = data.config.deploy[0];
            if (deploy.autoRelease) {
              conf.autoRelease = true;
            }
          }
          if (data.config.controlConfig) {
            const control = data.config.controlConfig[0];
            if (control.controlUrl) {
              conf.controlUrl = control.controlUrl[0];
            }
            if (control.controlUsername) {
              conf.controlUsername = control.controlUsername[0];
            }
            if (control.controlPassword) {
              conf.controlPassword = control.controlPassword[0];
            }
          }

          resolve(conf);
        }
      );
    });
  });
};
