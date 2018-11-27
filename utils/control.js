const fetch = require("node-fetch");
const urljoin = require("url-join");
const fs = require("fs");
const FormData = require("form-data");

const CONTROL_API_BASE = (exports.CONTROL_API_BASE = "/wiff.php");

//Generate the control arguments, for all cli that need control access
exports.controlArguments = parameters => {
  return Object.assign(parameters, {
    controlUrl: {
      defaultDescription: "control url",
      alias: "c",
      type: "string",
      demandOption: "You must provide the url of control"
    },
    controlUsername: {
      defaultDescription: "control username",
      alias: "u",
      type: "string",
      demandOption: "You must provide the username of control"
    },
    controlPassword: {
      defaultDescription: "control password",
      alias: "p",
      type: "string",
      demandOption: "You must provide the password of control"
    }
  });
};

//Generate the basic header for control connexion
const getBaseAutorisation = (exports.getBaseAutorisation = (
  username,
  password
) => {
  return "Basic " + Buffer.from(username + ":" + password).toString("base64");
});

//Handle second level control API error
const analyzeApiReturn = result => {
  if (!result.success) {
    throw new Error("Control error : " + result.error);
  }
  return result.data;
};

/**
 * Check if login, password and url are OK
 * @param {*} param0
 */
exports.checkControlConnexion = ({
  controlUrl,
  controlUsername,
  controlPassword
}) => {
  const setupUrl = urljoin(controlUrl, CONTROL_API_BASE);
  const formData = new FormData();
  formData.append("version", "true");
  return fetch(setupUrl, {
    headers: {
      Authorization: getBaseAutorisation(controlUsername, controlPassword)
    },
    method: "POST",
    body: formData
  })
    .then(response => {
      if (!response.ok) {
        return response.text().then(contentText => {
          if (response.status === 401) {
            throw new Error(
              response.status +
                " " +
                response.statusText +
                " : you should check the login and password " +
                contentText
            );
          }
          throw new Error(
            response.status +
              " " +
              response.statusText +
              " : you should check the control url " +
              setupUrl +
              " " +
              contentText
          );
        });
      }
      return response.json();
    })
    .then(result => {
      return !!analyzeApiReturn(result);
    });
};

/**
 * Send the generated or gived module for upload
 * @param {*} param0
 */
exports.postModule = ({
  controlUrl,
  controlUsername,
  controlPassword,
  fileName,
  force,
  action,
  context
}) => {
  const formData = new FormData();
  formData.append("deployWebinst", "true");
  formData.append("context", context);
  formData.append("webinst", fs.createReadStream(fileName));
  if (action) {
    formData.append("action", action);
  }
  if (force) {
    formData.append("additional_args[force]", "yes");
  }
  return fetch(urljoin(controlUrl, CONTROL_API_BASE), {
    headers: {
      Authorization: getBaseAutorisation(controlUsername, controlPassword)
    },
    method: "POST",
    body: formData
  })
    .then(response => {
      if (!response.ok) {
        return response.text().then(contentText => {
          throw new Error(
            response.status + " " + response.statusText + contentText
          );
        });
      }
      return response.json();
    })
    .then(result => {
      if (result.error) {
        throw new Error(JSON.stringify(result));
      }
      return result;
    });
};
