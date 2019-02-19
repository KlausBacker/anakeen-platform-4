const path = require("path");
const tarstream = require("tar-stream");
const gunzip = require("gunzip-maybe");
const fs = require("fs");
const { Writable } = require("stream");

const GenericError = require(path.resolve(__dirname, "GenericError.js"));

class BufferedWriteStream extends Writable {
  constructor(options) {
    super(options);
    this.reset();
  }

  _write(chunk, encoding, callback) {
    this._buff = Buffer.concat([this._buff, chunk]);
    callback();
  }

  reset() {
    this._buff = Buffer.alloc(0);
  }

  getBuffer() {
    return this._buff;
  }
}

class AppModuleFileError extends GenericError {}

class AppModuleFile {
  /**
   * @param {string} file
   */
  constructor(file) {
    this.file = file;
  }

  async getInfoXMLText() {
    const outputBuffer = new BufferedWriteStream();
    const extract = tarstream.extract();
    const input = fs.createReadStream(this.file);

    extract.on("entry", async (header, stream, next) => {
      stream.on("end", () => {
        next();
      });
      stream.resume();
      if (header.name.match(/^(?:\.\/)?info.xml$/)) {
        outputBuffer.reset();
        await new Promise((resolve, reject) => {
          try {
            stream
              .pipe(outputBuffer)
              .on("finish", resolve)
              .on("error", reject);
          } catch (e) {
            reject(e);
          }
        });
      }
    });

    await new Promise((resolve, reject) => {
      try {
        input
          .pipe(gunzip(1))
          .pipe(extract)
          .on("finish", resolve)
          .on("error", reject);
      } catch (e) {
        reject(e);
      }
    });

    return outputBuffer.getBuffer().toString();
  }
}

module.exports = { AppModuleFile, AppModuleFileError };
