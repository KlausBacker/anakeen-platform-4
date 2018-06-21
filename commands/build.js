const {build} = require("../tasks/build");
const signale = require("signale");

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
        const task = build(argv.sourceDir, argv.targetDir).tasks.build.fn;
        task();
        signale.success("build done");
    } catch (e) {
        signale.error(e);
    }
};