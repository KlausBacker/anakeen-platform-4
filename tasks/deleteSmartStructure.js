const gulp = require("gulp");
const DELETE_SMARTSTRUCTURE_API = "/api/v2/devel/smart/structure/delete";
const fetch = require("node-fetch");
const urlJoin = require("url-join");

//Generate the basic header for control connexion
const getBaseAutorisation = (exports.getBaseAutorisation = (
  username,
  password
) => {
  return "Basic " + Buffer.from(username + ":" + password).toString("base64");
});

exports.deleteSmartStructure = ({ name, username, password, contextUrl }) => {
  return gulp.task("deleteSmartStructure", async () => {
    const url = `${urlJoin(
      contextUrl,
      DELETE_SMARTSTRUCTURE_API
    )}?name=${name}`;
    return fetch(url, {
      headers: {
        Authorization: getBaseAutorisation(username, password)
      },
      method: "DELETE"
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
  });
};
