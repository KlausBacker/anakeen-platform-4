window.dcp.document.documentController(
  "addEventListener",
  "attributeReady",
  {
    name: "array:titles:ready",
    documentCheck: documentObject => {
      return documentObject.renderMode === "edit";
    },
    attributeCheck: attributeObject => {
      return attributeObject.id === "hub_instance_titles";
    }
  },
  function(event, documentObject, attributeObject) {
    //   $(this).documentController("appendArrayRow", attributeObject.id, {
    //     hub_language: { value: "English" },
    //     hub_language_code: { value: "en-EN" }
    //   });
    //   $(this).documentController("appendArrayRow", attributeObject.id, {
    //     hub_language: { value: "Français" },
    //     hub_language_code: { value: "fr-FR" }
    //   });
  }
);
