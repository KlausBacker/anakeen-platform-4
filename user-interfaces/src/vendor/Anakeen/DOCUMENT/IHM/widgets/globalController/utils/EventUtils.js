export function getBeforeEventPromise(event, onBeforeContinue, onBeforePrevent) {
  // In all case return a promise
  return new Promise((resolve, reject) => {
    // Promise based prevent event
    if (event && event.promise && (event.promise instanceof Promise || typeof event.promise.then === "function")) {
      event.promise
        .then(() => {
          if (typeof onBeforeContinue === "function") {
            onBeforeContinue();
          }
          resolve();
        })
        .catch(err => {
          if (typeof onBeforePrevent === "function") {
            onBeforePrevent();
          }
          reject(new Error(err));
        });
    } else {
      // Traditional event prevent
      if (event.prevent === false) {
        if (typeof onBeforeContinue === "function") {
          onBeforeContinue();
        }
        resolve();
      } else {
        if (typeof onBeforePrevent === "function") {
          onBeforePrevent();
        }
        reject();
      }
    }
  });
}
