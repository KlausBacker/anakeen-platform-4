const { getModuleInfo } = require("../utils/moduleInfo");
const signale = require("signale");

exports.desc = "Analyze the module content";
exports.builder = {
  sourcePath: {
    defaultDescription: "path of the info.xml",
    alias: "s",
    default: ".",
    type: "string"
  }
};

exports.handler = async argv => {
  try {
    const info = await getModuleInfo(argv.sourcePath);
    for (let element in info.moduleInfo) {
      signale.info(element, " : ", info.moduleInfo[element]);
    }
    signale.success("Done");
  } catch (e) {
    signale.error(e);
  }
};
