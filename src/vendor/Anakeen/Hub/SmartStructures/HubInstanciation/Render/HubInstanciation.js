window.dcp.document.documentController(
  "addEventListener",
  "attributeReady",
  {
    name: "array:language:ready",
    documentCheck: documentObject => {
      return documentObject.renderMode === "edit";
    },
    attributeCheck: attributeObject => {
      return attributeObject.id === "hub_instance_language";
    }
  },
  function(event, documentObject, attributeObject) {
    $(this).documentController("setValue", attributeObject.id, [
      {
        value: "English",
        displayValue: "English",
        index: 0
      },
      {
        value: "Français",
        displayValue: "Français",
        index: 1
      }
    ]);
  }
);

window.dcp.document.documentController(
  "addEventListener",
  "afterSave",
  {
    name: "passToViewInstance",
    documentCheck: documentObject => {
      return documentObject.renderMode === "edit";
    }
  },
  function reloadInConsultation(event, currentDocumentObject) {
    this.documentController("fetchDocument", {
      initid: currentDocumentObject.id,
      viewId: "!defaultConsultation"
    });
  }
);
