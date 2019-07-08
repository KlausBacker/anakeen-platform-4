const fs = require("fs");
const xml2js = require("xml2js");

const CONF_NAME = "./.anakeen-cli.xml";

exports.autoconf = () => {
  return new Promise((resolve, reject) => {
    //read file
    if (!fs.existsSync(CONF_NAME)) {
      return resolve({});
    }

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
            return reject(err);
          }
          //convert xml data to arguments
          if (!data) {
            return resolve({});
          }
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
            if (control.controlContext) {
              conf.context = control.controlContext[0];
            }
          }

          if (data.config.contextConfig) {
            const context = data.config.contextConfig[0];
            if (context.contextUrl) {
              conf.contextUrl = context.contextUrl[0];
            }
            if (context.contextUsername) {
              conf.contextUsername = context.contextUsername[0];
            }
            if (context.contextPassword) {
              conf.contextPassword = context.contextPassword[0];
            }
          }

          if (data.config.composeConfig) {
            const compose = data.config.composeConfig[0];
            if (compose.localRepo) {
              conf.localRepo = compose.localRepo[0];
            }
            if (compose.localSrc) {
              conf.localSrc = compose.localSrc[0];
            }
          }
          resolve(conf);
        }
      );
    });
  });
};
