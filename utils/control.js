const fetch = require("node-fetch");
const fs = require("fs");

const CONTROL_API_BASE = (exports.CONTROL_API_BASE = "/api/v1");

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

const getBaseAutorisation = (exports.getBaseAutorisation = (
  username,
  password
) => {
  return "Basic " + Buffer.from(username + ":" + password).toString("base64");
});

exports.checkControlConnexion = ({
  controlUrl,
  controlUsername,
  controlPassword
}) => {
  const setupUrl = controlUrl + CONTROL_API_BASE + "/setup";
  return fetch(setupUrl, {
    headers: {
      Authorization: getBaseAutorisation(controlUsername, controlPassword)
    }
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
      if (!result || !result.success) {
        throw new Error("Unable to analyze control authent");
      }
      return true;
    });
};

exports.cleanTransaction = ({
  controlUrl,
  controlUsername,
  controlPassword,
  force
}) => {
  return fetch(controlUrl + CONTROL_API_BASE + "/transactions/0", {
    headers: {
      Authorization: getBaseAutorisation(controlUsername, controlPassword)
    }
  })
    .then(result => {
      if (result.status === 404) {
        return "no_transaction";
      }
      return result.json();
    })
    .then(result => {
      if (result === "no_transaction") {
        return true;
      }
      if (result.success !== true) {
        throw new Error(
          "Unable to get transaction info" + JSON.stringify(transaction)
        );
      }
      if (!force && result.data.status !== "end") {
        throw new Error(
          "There is already a deployment in progress (try with force)" +
            JSON.stringify(result)
        );
      }
      //Delete the old transaction sic...
      return fetch(controlUrl + CONTROL_API_BASE + "/transactions/0", {
        headers: {
          Authorization: getBaseAutorisation(controlUsername, controlPassword)
        },
        method: "DELETE"
      }).then(result => {
        if (result.status !== 200) {
          throw new Error("Unable to delete the previous transaction");
        }
        return true;
      });
    });
};

exports.postModule = ({
  controlUrl,
  controlUsername,
  controlPassword,
  appStream
}) => {
  return fetch(controlUrl + CONTROL_API_BASE + "/modules/", {
    headers: {
      Authorization: getBaseAutorisation(controlUsername, controlPassword)
    },
    method: "POST",
    body: appStream
  })
    .then(response => {
      if (!response.ok) {
        return response.text().then(contentText => {
          throw new Error(
            "Unable to send the module " +
              response.status +
              " " +
              response.statusText +
              " " +
              contentText
          );
        });
      }
      return response.json();
    })
    .then(result => {
      if (!result.success) {
        throw new Error("Module posted refused" + result.error.contentText);
      }
      return true;
    });
};

exports.checkTransaction = ({
  controlUrl,
  controlUsername,
  controlPassword
}) => {
  return fetch(controlUrl + CONTROL_API_BASE + "/transactions/0", {
    headers: {
      Authorization: getBaseAutorisation(controlUsername, controlPassword)
    }
  })
    .then(response => {
      if (!response.ok) {
        return response.text().then(contentText => {
          throw new Error(
            "Unable to read the transaction " +
              response.status +
              " " +
              response.statusText +
              " " +
              contentText
          );
        });
      }
      return response.json();
    })
    .then(result => {
      if (!result.success) {
        throw new Error(
          "Unable to read the transaction " + result.error.contentText
        );
      }
      return result.data;
    });
};

exports.nextStep = ({ controlUrl, controlUsername, controlPassword }) => {
  return fetch(controlUrl + CONTROL_API_BASE + "/transactions/0", {
    headers: {
      Authorization: getBaseAutorisation(controlUsername, controlPassword)
    },
    method: "POST"
  })
    .then(response => {
      if (!response.ok) {
        return response.text().then(contentText => {
          throw new Error(
            "Unable to execute the transaction " +
              response.status +
              " " +
              response.statusText +
              " " +
              contentText
          );
        });
      }
      return response.json();
    })
    .then(result => {
      if (!result.success) {
        throw new Error(
          "Unable to execute the transaction " + result.error.contentText
        );
      }
      return result.data;
    });
};
