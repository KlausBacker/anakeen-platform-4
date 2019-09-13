const DEFAULT_OPTIONS = {
  timeout: -1,
  loadingStart: () => {},
  loadingStop: () => {}
};

class TimeoutPromiseError extends Error {
  constructor(props) {
    super(props);
    this.name = "TimeoutPromiseError";
    this.type = "TIMEOUT";
  }
}

class TimeoutPromise {
  constructor(timeout = -1) {
    this.timeout = timeout;
  }

  launch() {
    if (this.timeout > -1) {
      this.timeoutPromise = new Promise((resolve, reject) => {
        if (this.timeout > -1) {
          this.timeoutId = window.setTimeout(() => {
            const error = new Error(`Timeout (${this.timeout} ms) reached`);
            reject(new TimeoutPromiseError(error.message));
          }, this.timeout);
        }
      });
    }
    return this.timeoutPromise;
  }

  stop() {
    if (this.timeout > -1) {
      window.clearTimeout(this.timeoutId);
      // garbage collect
      this.timeoutPromise = null;
    }
  }
}

export function getBeforeEventPromise(event, onBeforeContinue, onBeforePrevent, options = DEFAULT_OPTIONS) {
  const promiseOptions = Object.assign({}, DEFAULT_OPTIONS, options);
  let result;
  const timer = new TimeoutPromise(promiseOptions.timeout);
  // In all case return a promise
  return new Promise((resolve, reject) => {
    // Promise based prevent event
    if (event && event.promise && (event.promise instanceof Promise || typeof event.promise.then === "function")) {
      if (promiseOptions && typeof promiseOptions.loadingStart === "function") {
        promiseOptions.loadingStart();
      }
      const promises = [event.promise];
      if (promiseOptions.timeout > -1) {
        promises.push(timer.launch());
      }
      Promise.race(promises)
        .then(eventResolveArg => {
          timer.stop();
          if (promiseOptions && typeof promiseOptions.loadingStop === "function") {
            promiseOptions.loadingStop();
          }
          let result;
          if (typeof onBeforeContinue === "function") {
            try {
              result = onBeforeContinue();
              if (result instanceof Promise) {
                result.then(() => resolve(eventResolveArg)).catch(reject);
              } else {
                resolve(eventResolveArg);
              }
            } catch (callbackError) {
              reject(callbackError);
            }
          }
        })
        .catch(eventError => {
          timer.stop();
          if (promiseOptions && typeof promiseOptions.loadingStop === "function") {
            promiseOptions.loadingStop();
          }
          if (typeof onBeforePrevent === "function") {
            try {
              result = onBeforePrevent();
              if (eventError) {
                // if event promise reject with message consider it as preventDefault + error
                reject(new Error(`[SmartElementController][${event.eventType}]: ${eventError.message || eventError}`));
              } else {
                // if event promise reject without message consider it as just preventDefault
                resolve(result);
              }
            } catch (callbackError) {
              reject(callbackError);
            }
          }
        });
    } else {
      // Traditional event prevent
      if (event.prevent === false) {
        if (typeof onBeforeContinue === "function") {
          try {
            result = onBeforeContinue();
            resolve(result);
          } catch (callbackError) {
            reject(callbackError);
          }
        }
      } else {
        if (typeof onBeforePrevent === "function") {
          try {
            result = onBeforePrevent();
            resolve(result);
          } catch (callbackError) {
            reject(callbackError);
          }
        }
      }
    }
  });
}
