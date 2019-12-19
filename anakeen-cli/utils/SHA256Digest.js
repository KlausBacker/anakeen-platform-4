const fs = require("fs");
const crypto = require("crypto");
const util = require("util");
const access = util.promisify(fs.access);

class SHA256Digest {
  static async file(filename) {
    const hash = crypto.createHash("sha256");

    //Test if file exist
    await access(filename);

    const inputStream = fs.createReadStream(filename);

    return new Promise((resolve, reject) => {
      inputStream.on("data", chunk => {
        hash.update(chunk);
      });
      inputStream.on("error", () => {
        reject(`Error reading content from '${filename}'`);
      });
      inputStream.on("end", () => {
        resolve(hash.digest("hex"));
      });
    });
  }
}

module.exports = SHA256Digest;
