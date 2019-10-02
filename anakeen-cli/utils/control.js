const fetch = require("node-fetch");
const urljoin = require("url-join");
const fs = require("fs");
const { Signale } = require("signale");
const controlLog = new Signale({ interactive: true, scope: "deploy" });

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
const getBaseAutorisation = (exports.getBaseAutorisation = (username, password) => {
  return "Basic " + Buffer.from(username + ":" + password).toString("base64");
});

/**
 * Check if login, password and url are OK
 * @param {*} param0
 */
exports.getControlStatus = async ({ controlUrl, controlUsername, controlPassword }) => {
  const setupUrl = urljoin(controlUrl, "api/status");

  return fetch(setupUrl, {
    headers: {
      Authorization: getBaseAutorisation(controlUsername, controlPassword)
    },
    method: "GET"
  }).then(response => {
    if (response.status !== 200) {
      return response.text().then(contentText => {
        if (response.status === 401) {
          throw new Error(
            response.status + " " + response.statusText + " : you should check the login and password " + contentText
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
  });
};

/**
 * Send the generated or gived module for upload
 * @param {*} param0
 */
exports.postModule = ({ controlUrl, controlUsername, controlPassword, fileName, reinstall = false }) => {
  const stats = fs.statSync(fileName);
  const fileSizeInBytes = stats.size;

  return fetch(urljoin(controlUrl, "/api/modules"), {
    headers: {
      "Content-length": fileSizeInBytes,
      Authorization: getBaseAutorisation(controlUsername, controlPassword),
      "X-ForceInstall": reinstall
    },
    method: "POST",
    body: fs.createReadStream(fileName)
  })
    .then(response => {
      if (response.status !== 200) {
        // throw new Error(response.json());
      }
      return response.json();
    })
    .then(result => {
      if (result.exceptionMessage) {
        throw new Error(result.exceptionMessage);
      }
      return result;
    })
    .catch(response => {
      throw new Error(response);
    });
};

exports.postModuleAndWaitTheEnd = async args => {
  return new Promise((resolve, reject) => {
    controlLog.await("Upload module to the server");
    exports
      .postModule(args)
      .catch(e => {
        reject(e);
      })
      .then(async result => {
        try {
          let running = true;
          let controlStatus;
          while (running) {
            await sleep(1000);
            controlStatus = await exports.getControlStatus(args);
            let runningTask = getRunningTask(controlStatus);

            if (runningTask) {
              controlLog.await(
                `${runningTask.action} : ${runningTask.module} - ${runningTask.phase} - ${runningTask.process}`
              );
              result.message = `Control status: ${runningTask.action}`;
            }
            running = controlStatus.status === "Running";
          }
          controlLog.note(`Deployment process is finished`);

          if (controlStatus.status === "Failed") {
            result.error = getStatusError(controlStatus);
            result.status = controlStatus;
            result.message = result.error.message;
            reject(result);
          } else {
            resolve(result);
          }
        } catch (e) {
          reject(e);
        }
      });
  });
};

const sleep = ms => {
  return new Promise(resolve => {
    setTimeout(resolve, ms);
  });
};

const getRunningTask = runningStatus => {
  const tasks = runningStatus.tasks || [];

  let msg = {
    action: runningStatus.status,
    module: "",
    phase: "",
    process: ""
  };

  tasks.forEach(task => {
    if (task.status === "Running") {
      msg.module = task.module;
      const phases = task.phases || [];
      phases.forEach(phase => {
        if (phase.status === "Running") {
          msg.phase = phase.name;
          const processes = phase.process || [];
          Object.keys(processes).forEach(function(key) {
            let process = processes[key];
            if (process.status === "Running") {
              msg.process = process.label;
            }
          });
        }
      });
    }
  });

  return msg;
};

const getStatusError = controlStatus => {
  const tasks = controlStatus.tasks || [];

  let msg = {
    action: controlStatus.status,
    module: "",
    phase: "",
    process: "",
    error: controlStatus.error || ""
  };

  tasks.forEach(task => {
    if (task.status !== "Running") {
      msg.module = task.module;
      if (task.error) {
        msg.error += task.error;
      }
      const phases = task.phases || [];
      phases.forEach(phase => {
        if (phase.status !== "Running") {
          msg.phase = phase.name;
          if (phase.error) {
            msg.error += phase.error;
          }
          const processes = phase.process || [];
          Object.keys(processes).forEach(function(key) {
            let process = processes[key];
            if (process.status === "Failed") {
              msg.process = process.label;
              msg.error += process.error;
            }
          });
        }
      });
    }
  });

  msg.error = msg.error.replace("\\n", "\n");
  msg.message = `${msg.module} ${msg.phase} ${msg.process} --  ${msg.error}`;
  return msg;
};
