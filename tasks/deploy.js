const gulp = require("gulp");
const asyncCallback = require("./plugins/asyncCallback");
const control = require("../utils/control");
const { Signale } = require("signale");
const { buildPipe } = require("./build");

const executeTransaction = async ({
  log,
  transaction,
  controlUrl,
  controlUsername,
  controlPassword
}) => {
  const currentOperation =
    transaction.operations[transaction.currentOperation].label;
  log("Execute current operation : " + currentOperation);
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
    nextTransaction.status === "pause"
  ) {
    log("Operation : " + currentOperation + " OK");
    await executeTransaction({
      log,
      transaction,
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
  log
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
      asyncCallback(async file => {
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
          controlPassword
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
  force
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
          log
        })
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
          interactive.await(message);
        };
        const pipe = await buildPipe({ sourcePath, autoRelease });
        deployPipe({
          gulpSrc: pipe,
          controlUrl,
          controlUsername,
          controlPassword,
          force,
          errorCallback: reject,
          log
        })
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
