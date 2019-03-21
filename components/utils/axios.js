import Axios from "axios";

const axios = Axios.create({
  baseURL: "/",
  timeout: 10000
});

// Intercept network errors from axios instance
const bindNetworkCommonsErrors = axiosInstance => {
  axiosInstance.interceptors.response.use(
    response => {
      if (response.headers && response.config) {
        if (
          response.config.headers &&
          response.config.headers.Accept &&
          response.config.headers.Accept.indexOf("application/json") > -1 &&
          response.headers["content-type"].indexOf("application/json") > -1 &&
          response.request &&
          response.request.responseText
        ) {
          // Check json format response
          try {
            JSON.parse(response.request.responseText);
          } catch (err) {
            console.error(
              `JSON parsing response error for request : ${
                response.request.responseURL
              }`
            );
            throw err;
          }
        }
      }
      return response;
    },
    error => {
      if (!error.response && error.request) {
        // The request was made but no response was received
        // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
        // http.ClientRequest in node.js
        console.error(
          "Looks like there are some network troubles",
          error.request
        );
      }
      return Promise.reject(error);
    }
  );
};

bindNetworkCommonsErrors(axios);

export default axios;
