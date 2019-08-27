const DEFAULT_OPTIONS = {
  timeout: 10000,
  loadingStart: () => {},
  loadingStop: () => {}
};

export function getBeforeEventPromise(event, onBeforeContinue, onBeforePrevent, options = DEFAULT_OPTIONS) {
  const promiseOptions = Object.assign({}, DEFAULT_OPTIONS, options);
  let result;
  // In all case return a promise
  return new Promise((resolve, reject) => {
    // Promise based prevent event
    if (event && event.promise && (event.promise instanceof Promise || typeof event.promise.then === "function")) {
      if (promiseOptions && typeof promiseOptions.loadingStart === "function") {
        promiseOptions.loadingStart();
      }
      const timeoutId = window.setTimeout(() => {
        if (promiseOptions && typeof promiseOptions.loadingStop === "function") {
          promiseOptions.loadingStop();
        }
        const error = new Error(
          `[Smart Element Controller][${event.eventType}] : Timeout (${promiseOptions.timeout} ms) reached`
        );
        console.error(error.message);
        reject(error);
      }, promiseOptions.timeout);
      event.promise
        .then(() => {
          window.clearTimeout(timeoutId);
          if (promiseOptions && typeof promiseOptions.loadingStop === "function") {
            promiseOptions.loadingStop();
          }
          let result;
          if (typeof onBeforeContinue === "function") {
            result = onBeforeContinue();
          }
          resolve(result);
        })
        .catch(err => {
          window.clearTimeout(timeoutId);
          if (promiseOptions && typeof promiseOptions.loadingStop === "function") {
            promiseOptions.loadingStop();
          }
          if (typeof onBeforePrevent === "function") {
            onBeforePrevent();
          }
          reject(new Error(err));
        });
    } else {
      // Traditional event prevent
      if (event.prevent === false) {
        if (typeof onBeforeContinue === "function") {
          result = onBeforeContinue();
        }
        resolve(result);
      } else {
        if (typeof onBeforePrevent === "function") {
          onBeforePrevent();
        }
        reject();
      }
    }
  });
}
