const gulp = require("gulp");
const fs = require("fs");
const { Signale } = require("signale");
const fetch = require("node-fetch");
const urlJoin = require("url-join");
const FormData = require("form-data");
const globFunction = require("glob");
const path = require("path");

const DEPLOY_CONFIG_API = "/api/v2/devel/import/configuration/";

//Generate the basic header for control connexion
const getBaseAutorisation = (exports.getBaseAutorisation = (
  username,
  password
) => {
  return "Basic " + Buffer.from(username + ":" + password).toString("base64");
});

// Deploy the sourcePath configuration file to contextUrl
const postFile = ({
  sourcePath,
  contextUrl,
  contextUsername,
  contextPassword,
  verbose,
  dryRun,
  log
}) => {
  const formData = new FormData();
  formData.append("file", fs.createReadStream(sourcePath));
  const url = `${urlJoin(
    contextUrl,
    DEPLOY_CONFIG_API
  )}?verbose=${verbose}&dryRun=${dryRun}`;
  return fetch(url, {
    headers: {
      Authorization: getBaseAutorisation(contextUsername, contextPassword)
    },
    method: "POST",
    body: formData
  })
    .then(response => {
      if (!response.ok) {
        return response.text().then(contentText => {
          if (response.status === 401 || response.status === 403) {
            throw new Error(
              response.status +
                " " +
                response.statusText +
                " : you should check the login and password " +
                contentText
            );
          }
          throw new Error(
            response.status + " " + response.statusText + contentText
          );
        });
      }

      return response.text().then(result => {
        try {
          return JSON.parse(result);
        } catch (e) {
          throw new Error(result);
        }
      });
    })
    .then(result => {
      if (!result.success) {
        throw new Error(JSON.stringify(result));
      }
      if (verbose) {
        log(result.messages.join("\n"));
      }
      return result;
    });
};

// Deploy the glob file configuration file to contextUrl
const postGlobFile = ({
  glob,
  sourceDir = process.cwd(),
  contextUrl,
  contextUsername,
  contextPassword,
  verbose,
  dryRun,
  log
}) => {
  const formData = new FormData();
  formData.append("file", fs.createReadStream(glob));
  const url = `${urlJoin(
    contextUrl,
    DEPLOY_CONFIG_API
  )}?verbose=${verbose}&dryRun=${dryRun}`;
  const globOpts = {
    nodir: true
  };
  if (sourceDir) {
    globOpts.cwd = sourceDir;
  }
  return new Promise((resolve, reject) => {
    globFunction(glob, globOpts, (err, files) => {
      if (err) {
        reject(err);
      } else {
        const formData = new FormData();
        files.forEach(file => {
          formData.append(
            "file[]",
            fs.createReadStream(path.resolve(sourceDir, file))
          );
        });
        fetch(url, {
          headers: {
            Authorization: getBaseAutorisation(contextUsername, contextPassword)
          },
          method: "POST",
          body: formData
        })
          .then(response => {
            if (!response.ok) {
              return response.text().then(contentText => {
                if (response.status === 401 || response.status === 403) {
                  throw new Error(
                    response.status +
                      " " +
                      response.statusText +
                      " : you should check the login and password " +
                      contentText
                  );
                }
                throw new Error(
                  response.status + " " + response.statusText + contentText
                );
              });
            }
            return response.json();
          })
          .then(result => {
            if (!result.success) {
              throw new Error(JSON.stringify(result));
            }
            if (verbose) {
              log(result.messages.join("\n"));
            }
            resolve(result);
          })
          .catch(err => {
            reject(err);
          });
      }
    });
  });
};

exports.deployConfiguration = ({
  fileToDeploy,
  contextUrl,
  contextUsername,
  contextPassword,
  verbose,
  dryRun
}) => {
  return gulp.task("importConfiguration", () => {
    // eslint-disable-next-line no-async-promise-executor
    return new Promise(async (resolve, reject) => {
      try {
        const interactive = new Signale({ scope: "deploy" });
        const log = message => {
          interactive.info(message);
        };
        return postFile({
          fileToDeploy,
          contextUrl,
          contextUsername,
          contextPassword,
          log,
          verbose,
          dryRun
        })
          .then(() => {
            interactive.success("Import config file done");
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

exports.deployGlobConfiguration = ({
  glob,
  sourceDir,
  contextUrl,
  contextUsername,
  contextPassword,
  verbose,
  dryRun
}) => {
  return gulp.task("importGlobConfiguration", () => {
    // eslint-disable-next-line no-async-promise-executor
    return new Promise(async (resolve, reject) => {
      try {
        const interactive = new Signale({ interactive: true, scope: "deploy" });
        const log = message => {
          interactive.info(message);
        };
        return postGlobFile({
          glob,
          sourceDir,
          contextUrl,
          contextUsername,
          contextPassword,
          log,
          verbose,
          dryRun
        })
          .then(() => {
            interactive.success("Import config file done");
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
