export const onAuthError = store => {
  store.dispatch("showModal", {
    template: `<div>It looks like the session has expired or you have been disconnected.</div>`,
    title: "Accès refusé",
    actions: [
      {
        text: "Réessayer",
        action: () => {
          // Do something to retry
          return true;
        }
      },
      {
        text: "Go to login",
        action: () => {
          window.location.reload();
        },
        primary: true
      }
    ]
  });
};

export const onNetworkError = store => {
  store.dispatch("showModal", {
    template: `<div>A network error has occurred. Try to reload the page or check your network configuration.</div>`,
    title: "Accès refusé",
    actions: [
      {
        text: "Cancel",
        action: () => {
          // Do something to retry
          return true;
        }
      },
      {
        text: "Reload",
        action: () => {
          window.location.reload();
        },
        primary: true
      }
    ]
  });
};

export const notify = r => {
  let message = "";
  if (r && r.response && r.response.status) {
    message = "Error " + r.response.status;
  } else {
    message = "no connection";
  }
  document.querySelector(".ank-notifier").dispatchEvent(
    new CustomEvent("ankNotification", {
      detail: [
        {
          content: {
            title: "Network error",
            textContent: message
          },
          type: "error"
        }
      ]
    })
  );
  return r;
};