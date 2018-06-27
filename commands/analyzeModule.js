const { getModuleInfo } = require("../utils/moduleInfo");
const signale = require("signale");

exports.desc = "Build the app file";
exports.builder = {
  sourceDir: {
    defaultDescription: "path of the info.xml",
    alias: "s",
    default: ".",
    type: "string"
  }
};

exports.handler = async argv => {
  try {
    const info = await getModuleInfo(argv.sourceDir);
    for (let element in info.moduleInfo) {
      signale.info(element, " : ", info.moduleInfo[element]);
    }
    signale.success("Done");
  } catch (e) {
    signale.error(e);
  }
};
