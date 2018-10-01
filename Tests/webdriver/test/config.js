const axios = require("axios");

exports.login = ({ browser, login, password }) => {
  return new Promise((resolve, reject) => {
    browser.url("/login/");
    axios({
      method: "post",
      url: `${browser.options.baseUrl}/api/v2/authent/sessions/${login}`,
      data: {
        password: password
      }
    })
      .then(response => {
        if (response.headers["set-cookie"]) {
          const promises = [];
          response.headers["set-cookie"].forEach(currentCookie => {
            const cookiesElement = currentCookie.split(";");
            const cookiePart = cookiesElement[0].split("=");
            promises.push(
              browser.setCookie({
                name: cookiePart[0],
                value: cookiePart[1]
              })
            );
            Promise.all(promises)
              .then(resolve)
              .catch(reject);
          });
        } else {
          reject("Login cookie not found");
        }
      })
      .catch(err => {
        console.error(err);
        reject(err);
      });
  });
};

exports.setWindowSize = ({ browser, width = 1024, height = 768 }) => {
  browser.url("/");
  browser.setViewportSize({
    width,
    height
  });
};
