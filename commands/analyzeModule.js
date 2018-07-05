const signale = require('signale');
const { getModuleInfo } = require('../utils/moduleInfo');

exports.desc = 'Analyze the module content';
exports.builder = {
  sourcePath: {
    defaultDescription: 'path of the info.xml',
    alias: 's',
    default: '.',
    type: 'string',
  },
};

exports.handler = async (argv) => {
  try {
    const info = await getModuleInfo(argv.sourcePath);
    const keys = Object.keys(info.moduleInfo);
    keys.forEach((currentKey) => {
      signale.info(currentKey, ' : ', info.moduleInfo[currentKey]);
    });
    signale.success('Done');
  } catch (e) {
    signale.error(e);
  }
};
