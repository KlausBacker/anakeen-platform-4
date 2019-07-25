const gulp = require("gulp");
const fetch = require("node-fetch");
const urlJoin = require("url-join");
const signale = require("signale");
//Generate the basic header for control connexion
const getBaseAutorisation = (exports.getBaseAutorisation = (username, password) => {
  return "Basic " + Buffer.from(username + ":" + password).toString("base64");
});

exports.deleteSmartStructure = ({ name, contextUsername, contextPassword, contextUrl }) => {
  return gulp.task("deleteSmartStructure", async () => {
    const DELETE_SMARTSTRUCTURE_API = `/api/v2/devel/smart/structures/${name}`;
    const url = `${urlJoin(contextUrl, DELETE_SMARTSTRUCTURE_API)}`;
    return fetch(url, {
      headers: {
        Authorization: getBaseAutorisation(contextUsername, contextPassword)
      },
      method: "DELETE"
    })
      .then(response => {
        signale.info(response);
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
            throw new Error(response.status + " " + response.statusText + contentText);
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
  });
};
