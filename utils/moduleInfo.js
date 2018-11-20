const fs = require("fs");
const path = require("path");
const appConst = require("./appConst");
const xml2js = require("xml2js");
const libxml = require("libxmljs");

const buildXSD = path.resolve(__dirname, "../xsd/anakeen-cli-build.xsd");

exports.getModuleInfo = async sourcePath => {
  let existsSourcePath = fs.existsSync(sourcePath);
  let existsInfoPath = fs.existsSync(path.join(sourcePath, appConst.infoPath));
  let existsBuildPath = fs.existsSync(
    path.join(sourcePath, appConst.buildPath)
  );

  if (!existsSourcePath) {
    throw new Error(`Unable to find the source "${sourcePath}"`);
  }
  if (!existsInfoPath) {
    throw new Error(
      `Unable to find the source info "${path.join(
        sourcePath,
        appConst.infoPath
      )}"`
    );
  }
  if (!existsBuildPath) {
    throw new Error(
      `Unable to find the source build "${path.join(
        sourcePath,
        appConst.buildPath
      )}"`
    );
  }
  //Check build path against xsd
  const xsd = libxml.parseXml(fs.readFileSync(buildXSD));
  const buildXML = libxml.parseXml(
    fs.readFileSync(path.join(sourcePath, appConst.buildPath))
  );

  if (buildXML.validate(xsd) === false) {
    return Promise.reject(buildXML.validationErrors);
  }

  return Promise.all([
    new Promise((resolve, reject) => {
      fs.readFile(
        path.join(sourcePath, appConst.infoPath),
        { encoding: "utf8" },
        (err, content) => {
          if (err) reject(err);
          xml2js.parseString(
            content,
            { tagNameProcessors: [xml2js.processors.stripPrefix] },
            (err, data) => {
              if (err) reject(err);
              const result = {};
              result.vendor = data.module.$.vendor;
              result.name = data.module.$.name;
              result.version = data.module.$.version;
              result.release = data.module.$.release;
              resolve(result);
            }
          );
        }
      );
    }),
    new Promise((resolve, reject) => {
      fs.readFile(
        path.join(sourcePath, appConst.buildPath),
        { encoding: "utf8" },
        (err, content) => {
          if (err) return reject(err);
          xml2js.parseString(
            content,
            { tagNameProcessors: [xml2js.processors.stripPrefix] },
            (err, data) => {
              if (err) return reject(err);
              const buildPath = [
                path.join(sourcePath, data.config.source[0].$.path)
              ];
              resolve({ build: data, buildPath });
            }
          );
        }
      );
    })
  ]).then(results => {
    return {
      sourcePath: sourcePath,
      moduleInfo: results[0],
      buildInfo: results[1]
    };
  });
};
