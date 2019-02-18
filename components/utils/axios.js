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
      if (error.response) {
        // The request was made and the server responded with a status code
        // that falls out of the range of 2xx
        if (
          error.response.data.message ||
          error.response.data.exceptionMessage
        ) {
          console.error(
            error.response.data.message || error.response.data.exceptionMessage
          );
        }
        if (error.response.data.error) {
          console.error(error.response.data.error);
        }
        console.error(JSON.stringify(error.response));
      } else if (error.request) {
        // The request was made but no response was received
        // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
        // http.ClientRequest in node.js
        console.error(
          "Looks like there are some network troubles",
          error.request
        );
      } else {
        // Something happened in setting up the request that triggered an Error
        console.error("Error", error.message);
      }
      return Promise.reject(error);
    }
  );
};

bindNetworkCommonsErrors(axios);

export default axios;
