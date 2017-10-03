

window.dcp.document.documentController("addEventListener",
  "ready",
  function(event, documentObject, message) {
    var classes=$(this).attr("class").split(" ");
      for (var i=0; i< classes.length; i++) {
          if (classes[i].substr(0,6) === "view--") {
              $(this).removeClass(classes[i]);
          }
      }
      $(this).addClass("view--"+documentObject.viewId);
  }
);