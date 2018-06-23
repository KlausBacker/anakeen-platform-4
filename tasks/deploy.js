const gulp = require('gulp');
const control = require('../utils/control');

const {Signale} = require('signale');

const executeTransaction = async ({log, controlUrl, controlUsername, controlPassword}) => {
    log.await("check transaction status");
    const transaction = await control.checkTransaction({controlUrl, controlUsername, controlPassword});
    log.success("Transaction ok");
    if (transaction.status === "ready") {
        const currentOperation = transaction.operations[transaction.currentOperation].label;
        log.await("Execute current operation "+currentOperation);
        const nextTransaction = await control.nextStep({controlUrl, controlUsername, controlPassword});
        if (nextTransaction.status === "ready") {
            log.success("Operation"+currentOperation+" OK");
            await executeTransaction({controlUrl, controlUsername, controlPassword});
        }
    }
}

exports.deploy = ({appPath = './', controlUrl, controlUsername, controlPassword, force}) => {

    return gulp.task("deploy", async () => {
        const interactive = new Signale({interactive: true, scope: 'deploy'});

        interactive.await("Test control connexion");
        const authent = await control.checkControlConnexion({controlUrl, controlUsername, controlPassword});
        if (authent) {
            interactive.success("Control connexion OK");
            interactive.await("Clean previous transaction");
            const cleanTransaction = await control.cleanTransaction({controlUrl, controlUsername, controlPassword, force});
            if (cleanTransaction) {
                interactive.success("Previous transaction cleaned");
                interactive.await("Send module to deploy", appPath);
                const postModule = await control.postModule({controlUrl, controlUsername, controlPassword, appPath});
                if (postModule) {
                    interactive.success("Module sended");
                    return await executeTransaction({log: interactive, controlUrl, controlUsername, controlPassword});
                }
            }
        }
    });

};