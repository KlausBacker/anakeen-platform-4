onconnect = function(e) {
  var port = e.ports[0];

  //Send a request to unlock an initid
  port.onmessage = function(e) {
    const event = e.data;
    if (event.type === "unlock") {
      const initid = event.initid;
      fetch(`/api/v2/smart-elements/${initid}/locks/temporary`, {
        method: "DELETE",
        credentials: "same-origin",
        headers: {
          "Content-Type": "application/json"
        }
      })
        .then(event => {
        })
        .catch(event => {
          console.log("Error", event);
        });
    }
  };
};
