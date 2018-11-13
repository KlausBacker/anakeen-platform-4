import * as mutationTypes from "../../store/mutation-types";

// Manage App Errors
export default class ErrorManager {
  constructor(appVueInstance) {
    this.appInstance = appVueInstance;
    if (!this.appInstance) {
      throw "[ErrorManager] : Unable to retrieve the instance of the vue app";
    }
  }

  // Intercept network errors from axios instance
  bindNetworkCommonsErrors() {
    this.appInstance.$http.interceptors.response.use(
      response => {
        if (response.headers) {
          if (
            response.headers["content-type"].indexOf("application/json") > -1 &&
            response.request &&
            response.request.responseText
          ) {
            try {
              JSON.parse(response.request.responseText);
            } catch (err) {
              console.error(
                `JSON parsing response error for request : ${response.request.toString()}`
              );
              return Promise.reject(err);
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
            this.appInstance.$store.dispatch("displayError", {
              title: "Server Error",
              textContent:
                error.response.data.message ||
                error.response.data.exceptionMessage
            });
          }
          if (error.response.data.error) {
            this.appInstance.$store.dispatch("displayError", {
              title: "Server Error",
              textContent: error.response.data.error
            });
          }
          console.error(error.response);
        } else if (error.request) {
          // The request was made but no response was received
          // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
          // http.ClientRequest in node.js
          this.appInstance.$store.dispatch("displayError", {
            title: "Network Error",
            textContent: "Looks like there are some network troubles"
          });
          console.error(error.request);
        } else {
          // Something happened in setting up the request that triggered an Error
          console.error("Error", error.message);
        }
        return Promise.reject(error);
      }
    );
  }

  // Bind notifier component to the error store mutation
  bindNotifier() {
    this.appInstance.$store.subscribe((mutation, state) => {
      switch (mutation.type) {
        case mutationTypes.SET_ERROR:
          if (state.app.error && state.app.error !== {}) {
            this.appInstance.$refs.ankNotifier.publishNotification(
              new CustomEvent("ankNotification", {
                detail: [
                  {
                    content: state.app.error,
                    type: state.app.error.type || "error"
                  }
                ]
              })
            );
          }
          break;
      }
    });
  }
}
