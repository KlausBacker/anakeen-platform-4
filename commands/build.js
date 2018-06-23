const {build} = require("../tasks/build");
const signale = require("signale");

signale.config({
    displayTimestamp: true,
    displayDate: true
  });

exports.desc = 'Build the app file';
exports.builder = {
    sourceDir: {
        defaultDescription: 'path of the info.xml',
        alias: 's',
        default: '.',
        type: 'string'
    },
    targetDir: {
        defaultDescription: 'target path',
        alias: 't',
        default: '.',
        type: 'string'
    }
};

exports.handler = function (argv) {
    try {
        signale.time("build");
        const task = build({ sourcePath : argv.sourceDir, targetPath :argv.targetDir}).tasks.build.fn;
        task().then(() => {
            signale.timeEnd("build")
            signale.success("build done");
        }).catch((e) => {
            signale.error(e);
        });
    } catch (e) {
        signale.error(e);
    }
};