const fetch = require("node-fetch");
const urljoin = require("url-join");
const inquirer = require("inquirer");

const CONTROL_API_BASE = (exports.CONTROL_API_BASE = "/api/v1");

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

//Handle first level control API error (status and json analyze)
const handleAPIError = response => {
  if (!response.ok) {
    return response.text().then(contentText => {
      throw new Error(
        "Control API error : " +
          response.status +
          " " +
          response.statusText +
          " " +
          contentText
      );
    });
  }
  return response.json();
};

//Handle second level control API error
const analyzeApiReturn = result => {
  if (!result.success) {
    throw new Error("Control error : " + result.error.contentText);
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
  const setupUrl = urljoin(controlUrl, CONTROL_API_BASE, "/setup");
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
      return !!analyzeApiReturn(result);
    });
};

/**
 * Clean a previous active transaction if needed
 * @param {*} param0 
 */
exports.cleanTransaction = ({
  controlUrl,
  controlUsername,
  controlPassword,
  force
}) => {
  return fetch(urljoin(controlUrl, CONTROL_API_BASE, "/transactions/0"), {
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
          "Unable to get transaction info" + JSON.stringify(result)
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

/**
 * Send the generated or gived module for upload
 * @param {*} param0 
 */
exports.postModule = ({
  controlUrl,
  controlUsername,
  controlPassword,
  appStream
}) => {
  return fetch(urljoin(controlUrl, CONTROL_API_BASE, "/modules/"), {
    headers: {
      Authorization: getBaseAutorisation(controlUsername, controlPassword)
    },
    method: "POST",
    body: appStream
  })
    .then(handleAPIError)
    .then(result => {
      return !!analyzeApiReturn(result);
    });
};

/**
 * Get and return a transaction
 * @param {*} param0 
 */
exports.checkTransaction = ({
  controlUrl,
  controlUsername,
  controlPassword
}) => {
  return fetch(urljoin(controlUrl, CONTROL_API_BASE, "/transactions/0"), {
    headers: {
      Authorization: getBaseAutorisation(controlUsername, controlPassword)
    }
  })
    .then(handleAPIError)
    .then(analyzeApiReturn);
};

/**
 * Go to the next step of a transaction
 * @param {*} param0 
 */
exports.nextStep = ({ controlUrl, controlUsername, controlPassword }) => {
  return fetch(urljoin(controlUrl, CONTROL_API_BASE, "/transactions/0"), {
    headers: {
      Authorization: getBaseAutorisation(controlUsername, controlPassword)
    },
    method: "POST"
  })
    .then(handleAPIError)
    .then(analyzeApiReturn);
};

/**
 * Add prompt to validate license
 * @param {*} param0 
 */
exports.validateLicenses = ({
  controlUrl,
  controlUsername,
  controlPassword
}) => {
  return fetch(urljoin(controlUrl, CONTROL_API_BASE, "/transactions/0"), {
    headers: {
      Authorization: getBaseAutorisation(controlUsername, controlPassword)
    },
    method: "GET"
  })
    .then(handleAPIError)
    .then(result => {
      const data = analyzeApiReturn(result);
      //If we are a not in licenses state, nothing to do
      if (data.status !== "licenses") {
        return true;
      }
      return Promise.all(
        data["module-licenses"].map(currentModule => {
          if (currentModule.accept) {
            return Promise.resolve();
          }
          return inquirer
            .prompt([
              {
                type: "confirm",
                name: "license",
                message: `Validate license for ${currentModule.module} ?`,
                default: false
              }
            ])
            .then(valid => {
              if (!valid.license) {
                return Promise.reject(
                  `License for ${currentModule.module} is not validated`
                );
              }
              return fetch(
                urljoin(
                  controlUrl,
                  CONTROL_API_BASE,
                  "/transactions/0/module-licenses/",
                  currentModule.module
                ),
                {
                  headers: {
                    Authorization: getBaseAutorisation(
                      controlUsername,
                      controlPassword
                    )
                  },
                  method: "PUT",
                  body: JSON.stringify({ accept: true })
                }
              )
                .then(handleAPIError)
                .then(analyzeApiReturn);
            });
        })
      );
    });
};

/**
 * Set the parameters of all the modules
 * @param {*} 
 */
exports.completeParameters = ({
  controlUrl,
  controlUsername,
  controlPassword,
  //Default parameters values
  parameterValues
}) => {
  //Get the current transaction value
  return fetch(urljoin(controlUrl, CONTROL_API_BASE, "/transactions/0"), {
    headers: {
      Authorization: getBaseAutorisation(controlUsername, controlPassword)
    },
    method: "GET"
  })
    .then(handleAPIError)
    .then(result => {
      const data = analyzeApiReturn(result);
      //If we are not in parameters state nothing to do
      if (data.status !== "parameters") {
        return true;
      }
      //Map all parameters asked and generate prompt if needed
      return Promise.all(
        data["module-parameters"].map(currentModuleParameters => {
          const prompt = currentModuleParameters.parameters
            .filter(currentParam => {
              return (
                //if parameter is set or has a value we don't prompt it
                currentParam.isset === false ||
                parameterValues[currentModuleParameters.module][
                  currentParam.name
                ]
              );
            })
            .map(currentParam => {
              return {
                type: currentParam.values ? "list" : "input",
                name: currentParam.name,
                message: `${currentParam.label} : `,
                choices: currentParam.values.split("|"),
                default: currentParam.default
              };
            });

          //Prompt the asked parameters and handle the result
          return inquirer.prompt(prompt).then(promptValues => {
            let i = 0;
            //Enhance the prompted response with parameters values
            const response = {...promptValues, ...parameterValues[currentModuleParameters.module]};
            const values = Object.keys(response);
            const initParameters = function setParameters() {
              return fetch(
                urljoin(
                  controlUrl,
                  CONTROL_API_BASE,
                  "/transactions/0/module-parameters/",
                  currentModuleParameters.module,
                  values[i]
                ),
                {
                  headers: {
                    Authorization: getBaseAutorisation(
                      controlUsername,
                      controlPassword
                    )
                  },
                  method: "PUT",
                  body: JSON.stringify({ value: response[values[i]] })
                }
              )
                .then(handleAPIError)
                .then(analyzeApiReturn)
                .then(() => {
                  i += 1;
                  if (values.length <= i) {
                    return Promise.resolve();
                  }
                  return setParameters();
                });
            };
            if (values.length === 0) {
              return Promise.resolve();
            }
            return initParameters();
          });
        })
      );
    });
};
