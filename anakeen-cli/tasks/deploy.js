const gulp = require("gulp");
const tmp = require("tmp");
const path = require("path");
const uuid_v4 = require("uuid/v4");
const control = require("../utils/control");
const { Signale } = require("signale");
const { buildPipe } = require("./build");

const produceApp = (gulpSrc, tmpDir) => {
  return new Promise((resolve, reject) => {
    gulpSrc
      .pipe(gulp.dest(tmpDir.name))
      .on("end", resolve)
      .on("error", reject);
  });
};

const deployPipe = (exports.deployPipe = async ({
  appPath,
  gulpSrc,
  localName,
  controlUrl,
  controlUsername,
  controlPassword,
  reinstall = false,
  log
}) => {
  let tmpDir = false;
  if (!appPath) {
    tmpDir = tmp.dirSync({
      unsafeCleanup: true
    });
    await produceApp(gulpSrc, tmpDir);
    appPath = path.join(tmpDir.name, localName + ".app");
  }
  log("Check control connexion");
  //Send gulpSrc to temp dest
  try {
    await control.getControlStatus({
      controlUrl,
      controlUsername,
      controlPassword
    });
    log("Post the module");
    if (reinstall) {
      log("Force installation mode");
    }
    const result = await control
      .postModuleAndWaitTheEnd({
        controlUrl,
        controlUsername,
        controlPassword,
        reinstall,
        fileName: appPath
      })
      .catch(e => {
        throw new Error(e.message);
      });
    log(result.message);
    if (tmpDir) {
      tmpDir.removeCallback();
    }
    return result;
  } catch (e) {
    throw new Error(e.message);
  }
});

exports.deploy = ({ appPath = "./", controlUrl, controlUsername, controlPassword, parameterValues }) => {
  return gulp.task("deploy", () => {
    return new Promise((resolve, reject) => {
      const interactive = new Signale({ scope: "deploy" });
      const log = message => {
        interactive.await(message);
      };
      deployPipe({
        appPath,
        controlUrl,
        controlUsername,
        controlPassword,
        log,
        parameterValues
      })
        .then(message => {
          if (message.data) {
            interactive.error(message.data.join(" "));
          }
          interactive.success("Deploy done");
          resolve();
        })
        .catch(error => {
          reject(error);
        });
    });
  });
};

exports.buildAndDeploy = ({
  sourcePath = ".",
  controlUrl,
  controlUsername,
  controlPassword,
  autoRelease = false,
  reinstall = false
}) => {
  return gulp.task("buildAndDeploy", () => {
    // eslint-disable-next-line no-async-promise-executor
    return new Promise(async (resolve, reject) => {
      try {
        const interactive = new Signale({ scope: "deploy" });
        const log = message => {
          interactive.info(message);
        };
        const localName = uuid_v4();
        const build = await buildPipe({
          sourcePath: sourcePath,
          autoRelease,
          localName
        });
        deployPipe({
          gulpSrc: build,
          localName,
          controlUrl,
          controlUsername,
          controlPassword,
          reinstall,
          errorCallback: reject,
          log
        })
          .then(() => {
            interactive.success("Deploy done");
            resolve();
          })
          .catch(error => {
            reject(error);
          });
      } catch (e) {
        reject(e);
      }
    });
  });
};
