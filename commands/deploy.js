const { deploy } = require("../tasks/deploy");
const signale = require("signale");
const { controlArguments } = require("../utils/control");

exports.desc = 'Deploy the app file';
exports.builder = controlArguments({
    appPath: {
        defaultDescription: 'application file path',
        alias: 't',
        demandOption: 'You must give the path of .app to deploy',
        type: 'string'
    },
    force: {
        defaultDescription: 'destroy already existing deployment',
        alias: 'f',
        default: false,
        type: 'boolean'
    }
});

exports.handler = function (argv) {
    try {
        const task = deploy(argv).tasks.deploy.fn;
        task().then(() => {
            signale.success("deploy done");
        }).catch((e) => {
            signale.error(e);
        });

    } catch (e) {
        signale.error(e);
    }
};