const fs = require("fs");
const path = require("path");
const appConst = require("./appConst");
const xml2js = require("xml2js");

exports.getModuleInfo = async (sourcePath) => {
    if (fs.existsSync(sourcePath)
        && fs.existsSync(path.join(sourcePath, appConst.infoPath))
        && fs.existsSync(path.join(sourcePath, appConst.buildPath))) {
        return Promise.all([new Promise((resolve, reject) => {
            fs.readFile(path.join(sourcePath, appConst.infoPath), { encoding: "utf8" }, (err, content) => {
                if (err) reject(err);
                xml2js.parseString(content, (err, data) => {
                    if (err) reject(err);
                    const result = {};
                    result.name = data.module.$.name;
                    result.version = data.module.$.version;
                    result.release = data.module.$.release;
                    result.description = data.module.description.join(" ");
                    resolve(result);
                });
            });

        }), new Promise((resolve, reject) => {
            fs.readFile(path.join(sourcePath, appConst.buildPath), { encoding: "utf8" }, (err, content) => {
                if (err) reject(err);
                xml2js.parseString(content, (err, data) => {
                    if (err) reject(err);
                    resolve(data);
                });
            });
        })]).then(results => {
            return {
                "moduleInfo": results[0],
                "buildInfo": results[1]
            }
        });
    } else {
        throw new Error("Unable to find the source" + sourcePath);
    }
};