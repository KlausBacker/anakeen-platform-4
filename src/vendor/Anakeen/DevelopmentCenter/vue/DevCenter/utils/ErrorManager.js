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
        return response;
      },
      error => {
        if (error.response) {
          // The request was made and the server responded with a status code
          // that falls out of the range of 2xx
          console.error(error.response.data);
          console.error(error.response.status);
          console.error(error.response.headers);
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
          console.log("Error", error.message);
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
