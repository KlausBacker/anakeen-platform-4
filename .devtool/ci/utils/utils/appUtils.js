const path = require("path");
const spawn = require("child_process").spawn;
const xml2js = require("xml2js");
const glob = require("glob");

const promiseSpawn = (command, args) => {
  return new Promise((resolve, reject) => {
    const result = spawn(command, args);
    let stdout = "";
    let stderr = "";
    result.stdout.on("data", data => {
      stdout += data;
    });
    result.stderr.on("data", data => {
      stderr += data;
    });
    result.on("close", code => {
      if (code === 0) {
        return resolve({
          code,
          stdout,
          stderr
        });
      }
      return reject({
        code,
        stderr,
        stdout
      });
    });
  });
};

module.exports.findApp = async (moduleName, modulePath) => {
  return new Promise((resolve, reject) => {
    glob(`${moduleName}*.app`, {
      cwd: path.resolve(modulePath),
      nodir: true
    },(err, values) => {
      if (err) {
        return reject(err);
      }
      const nameRegexp = new RegExp(`${moduleName}-[0-9]+.[0-9]+.[0-9]+.*.app`);
      return resolve(
        values.filter(currentValue => {
          return nameRegexp.test(currentValue);
        })[0]
      );
    });
  });
};

module.exports.getInfoFromApp = async appPath => {
  const info = await promiseSpawn("tar", [
    "--to-stdout",
    "--extract",
    `--file=${appPath}`,
    "info.xml"
  ]);
  return new Promise((resolve, reject) => {
    xml2js.parseString(
      info.stdout,
      { tagNameProcessors: [xml2js.processors.stripPrefix] },
      (err, data) => {
        if (err) {
          return reject(err);
        }
        const result = {};
        result.vendor = data.module.$.vendor;
        result.name = data.module.$.name;
        result.version = data.module.$.version;
        resolve(result);
      }
    );
  });
};

module.exports.promiseSpawn = promiseSpawn;
