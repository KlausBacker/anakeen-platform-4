const gulp = require("gulp");
const fs = require("fs");
const { Signale } = require("signale");
const fetch = require("node-fetch");
const urlJoin = require("url-join");
const FormData = require("form-data");

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
  username,
  password,
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
      Authorization: getBaseAutorisation(username, password)
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
      return result;
    });
};

exports.deployConfiguration = ({
  sourcePath,
  contextUrl,
  username,
  password,
  verbose,
  dryRun
}) => {
  return gulp.task("importConfiguration", () => {
    return new Promise(async (resolve, reject) => {
      try {
        const interactive = new Signale({ interactive: true, scope: "deploy" });
        const log = message => {
          interactive.info(message);
        };
        return postFile({
          sourcePath,
          contextUrl,
          username,
          password,
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
