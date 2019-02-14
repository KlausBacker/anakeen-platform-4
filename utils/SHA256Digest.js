const fs = require("fs");
const crypto = require("crypto");

class SHA256Digest {
  static async file(filename) {
    const hash = crypto.createHash("sha256");
    const inputStream = fs.createReadStream(filename);

    return await new Promise((resolve, reject) => {
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
