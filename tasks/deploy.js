const gulp = require("gulp");
const asyncCallback = require("./plugins/asyncCallback");
const endPipe = require("./plugins/end");
const control = require("../utils/control");
const { Signale } = require("signale");
const { buildPipe } = require("./build");

const executeTransaction = async ({
  log,
  transaction,
  controlUrl,
  controlUsername,
  controlPassword,
  parameterValues
}) => {
  if (transaction.status === "licenses") {
    await control.validateLicenses({
      controlUrl,
      controlUsername,
      controlPassword
    });
  }
  if (transaction.status === "parameters") {
    await control.completeParameters({
      controlUrl,
      controlUsername,
      controlPassword,
      parameterValues
    });
  }
  log(
    `Execute current operation : ${
      transaction.operations[transaction.currentOperation].label
    } (${transaction.currentOperation})`
  );
  const nextTransaction = await control.nextStep({
    controlUrl,
    controlUsername,
    controlPassword
  });
  if (nextTransaction.status === "end") {
    log("Deploy finished");
    return Promise.resolve("Deploy finished");
  }
  if (
    nextTransaction.status === "ready" ||
    nextTransaction.status === "pause" ||
    nextTransaction.status === "parameters" ||
    nextTransaction.status === "licenses"
  ) {
    log(
      `Operation : ${
        transaction.operations[transaction.currentOperation].label
      } OK (${transaction.currentOperation})`
    );
    await executeTransaction({
      log,
      transaction: nextTransaction,
      controlUrl,
      controlUsername,
      controlPassword
    });
  } else {
    throw new Error(
      "Unable to execute the transaction " + JSON.stringify(nextTransaction)
    );
  }
};

const deployPipe = (exports.deployPipe = ({
  gulpSrc,
  controlUrl,
  controlUsername,
  controlPassword,
  force,
  errorCallback,
  log,
  parameterValues
}) => {
  return gulpSrc
    .pipe(
      asyncCallback(async () => {
        log("Test control connexion");
        await control.checkControlConnexion({
          controlUrl,
          controlUsername,
          controlPassword
        });
      })
    )
    .on("error", error => {
      errorCallback(error);
    })
    .pipe(
      asyncCallback(async () => {
        log("Clean previous transaction");
        await control.cleanTransaction({
          controlUrl,
          controlUsername,
          controlPassword,
          force
        });
      })
    )
    .on("error", error => {
      errorCallback(error);
    })
    .pipe(
      asyncCallback(async files => {
        const file = files[0];
        if (file.isNull()) {
          return;
        }
        log("Post the module");
        await control.postModule({
          controlUrl,
          controlUsername,
          controlPassword,
          appStream: file.contents
        });
      })
    )
    .on("error", error => {
      errorCallback(error);
    })
    .pipe(
      asyncCallback(async () => {
        log("check transaction status");
        const transaction = await control.checkTransaction({
          controlUrl,
          controlUsername,
          controlPassword
        });
        log("Transaction ok");
        await executeTransaction({
          log,
          transaction,
          controlUrl,
          controlUsername,
          controlPassword,
          parameterValues
        });
      })
    )
    .on("error", error => {
      errorCallback(error);
    });
});

exports.deploy = ({
  appPath = "./",
  controlUrl,
  controlUsername,
  controlPassword,
  force,
  parameterValues
}) => {
  return gulp.task("deploy", () => {
    try {
      return new Promise((resolve, reject) => {
        const interactive = new Signale({ interactive: true, scope: "deploy" });
        const log = message => {
          interactive.await(message);
        };
        deployPipe({
          gulpSrc: gulp.src(appPath),
          controlUrl,
          controlUsername,
          controlPassword,
          force,
          errorCallback: reject,
          log,
          parameterValues
        })
          .pipe(endPipe())
          .on("end", () => {
            interactive.success("Deploy done");
            resolve();
          })
          .on("error", error => {
            reject(error);
          });
      });
    } catch (e) {
      throw e;
    }
  });
};

exports.buildAndDeploy = ({
  sourcePath = ".",
  controlUrl,
  controlUsername,
  controlPassword,
  force,
  autoRelease = false
}) => {
  return gulp.task("buildAndDeploy", () => {
    return new Promise(async (resolve, reject) => {
      try {
        const interactive = new Signale({ interactive: true, scope: "deploy" });
        const log = message => {
          interactive.info(message);
        };
        const build = await buildPipe({ sourcePath, autoRelease });
        deployPipe({
          gulpSrc: build,
          controlUrl,
          controlUsername,
          controlPassword,
          force,
          errorCallback: reject,
          log
        })
          .pipe(endPipe())
          .on("end", () => {
            interactive.success("Deploy done");
            resolve();
          })
          .on("error", error => {
            reject(error);
          });
      } catch (e) {
        reject(e);
      }
    });
  });
};
