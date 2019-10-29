const fs = require("fs");
const util = require("util");

const fs_stat = util.promisify(fs.stat);

class Utils {
  /**
   * Check if a file (or dir) exists
   * @param {string} filename
   * @returns {boolean|{fs.Stats}}
   */
  static async fileExists(filename) {
    try {
      return await fs_stat(filename);
    } catch (e) {
      return false;
    }
  }

  static normalizeUrl(url) {
    /* Normalize hostname (to lowercase) in URL */
    const u = new URL(url);
    /* Remove duplicates slashes */
    u.pathname = u.pathname.replace(/\/\/+/, "/").replace(/\/+$/, "");
    return u.toString();
  }
}

module.exports = Utils;
