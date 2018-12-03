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
  force,
  log,
  action,
  context
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
  await control.checkControlConnexion({
    controlUrl,
    controlUsername,
    controlPassword
  });
  log("Post the module");
  const result = await control.postModule({
    controlUrl,
    controlUsername,
    controlPassword,
    fileName: appPath,
    force,
    action,
    context
  });
  log(result.data.join("\n"));
  if (tmpDir) {
    tmpDir.removeCallback();
  }
  return result;
});

exports.deploy = ({
  appPath = "./",
  controlUrl,
  controlUsername,
  controlPassword,
  force,
  parameterValues,
  action,
  context
}) => {
  return gulp.task("deploy", () => {
    try {
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
          force,
          log,
          parameterValues,
          action,
          context
        })
          .then(message => {
            //console.log(message.data.join(" "));
            if (message.data) {
              //console.log(message.data.join(" "));
              interactive.error(message.data.join(" "));
            }
            interactive.success("Deploy done");
            resolve();
          })
          .catch(error => {
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
  context,
  force,
  autoRelease = false
}) => {
  return gulp.task("buildAndDeploy", () => {
    return new Promise(async (resolve, reject) => {
      try {
        const interactive = new Signale({ scope: "deploy" });
        const log = message => {
          interactive.info(message);
        };
        const localName = uuid_v4();
        const build = await buildPipe({ sourcePath, autoRelease, localName });
        deployPipe({
          gulpSrc: build,
          localName,
          controlUrl,
          controlUsername,
          controlPassword,
          force,
          context,
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
