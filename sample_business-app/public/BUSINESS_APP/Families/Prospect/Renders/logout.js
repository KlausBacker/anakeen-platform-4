window.dcp.document.documentController("addEventListener",
  "ready",
  {
      "name": "CSTB_PROSPECT::logout",
      "documentCheck": function (document) {
          return document.family.name === "CSTB_PROSPECT"
      }
  },
  function (event, currentDocumentObject) {
      console.log("LOGOUT", currentDocumentObject);

      if (currentDocumentObject.viewId === "ack") {

          console.log("LOGOUT CONFIRMED");
          var url = "?app=AUTHENT&action=LOGOUT";
          $.get(url);
      }
  }
);