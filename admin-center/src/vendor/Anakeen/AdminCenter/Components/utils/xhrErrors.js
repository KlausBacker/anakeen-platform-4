export const onAuthError = store => {
  store.dispatch("showModal", {
    template: `<div>It looks like the session has expired or you have been disconnected.</div>`,
    title: "Accès refusé",
    actions: [
      {
        text: "Réessayer",
        action: e => {
          // Do something to retry
          return true;
        }
      },
      {
        text: "Go to login",
        action: e => {
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
        action: e => {
          // Do something to retry
          return true;
        }
      },
      {
        text: "Reload",
        action: e => {
          window.location.reload();
        },
        primary: true
      }
    ]
  });
};
