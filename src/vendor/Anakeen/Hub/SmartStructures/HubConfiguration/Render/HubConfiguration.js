import "./HubConfiguration.css";

window.dcp.document.documentController(
  "addEventListener",
  "attributeReady",
  {
    name: "hub:configuration:view:ready",
    documentCheck: documentObject => {
      return documentObject.renderMode === "view";
    },
    attributeCheck: attributeObject => {
      return attributeObject.id === "hub_icon_enum";
    }
  },
  function() {
    switch ($(this).documentController("getValue", "hub_icon_enum").value) {
      case "HTML":
        console.log(
          $(this).documentController("getValue", "hub_icon_text").value
        );
        break;
      case "IMAGE":
        console.log(
          $(this).documentController("getValue", "hub_icon_image").value
        );
        break;
      case "FONT":
        console.log(
          $(this).documentController("getValue", "hub_icon_font").value
        );
        break;
      default:
        break;
    }
  }
);

window.dcp.document.documentController(
  "addEventListener",
  "afterSave",
  {
    name: "passToView",
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

window.dcp.document.documentController(
  "addEventListener",
  "change",
  {
    name: "icon:mode:changed",
    documentCheck: documentObject => {
      return documentObject.renderMode === "edit";
    },
    attributeCheck: attributeObject => {
      return attributeObject.id === "hub_icon_enum";
    }
  },
  function(event, documentObject, attributeObject, $el) {
    switch ($el.current.value) {
      case "IMAGE":
        $(".dcpAttribute[data-attrid='hub_icon_font']")[0].style.display =
          "none";
        $(".dcpAttribute[data-attrid='hub_icon_text']")[0].style.display =
          "none";
        $(".dcpAttribute[data-attrid='hub_icon_image']")[0].style.display =
          "inline";
        break;
      case "FONT":
        $(".dcpAttribute[data-attrid='hub_icon_image']")[0].style.display =
          "none";
        $(".dcpAttribute[data-attrid='hub_icon_text']")[0].style.display =
          "none";
        $(".dcpAttribute[data-attrid='hub_icon_font']")[0].style.display =
          "inline";
        break;
      case "HTML":
        $(".dcpAttribute[data-attrid='hub_icon_font']")[0].style.display =
          "none";
        $(".dcpAttribute[data-attrid='hub_icon_image']")[0].style.display =
          "none";
        $(".dcpAttribute[data-attrid='hub_icon_text']")[0].style.display =
          "inline";
        break;
      default:
        break;
    }
  }
);

window.dcp.document.documentController(
  "addEventListener",
  "attributeReady",
  {
    name: "icon:image:ready",
    documentCheck: documentObject => {
      return (
        documentObject.renderMode === "edit" ||
        documentObject.renderMode === "view"
      );
    },
    attributeCheck: attributeObject => {
      return attributeObject.id === "hub_icon_image";
    }
  },
  function(event, documentObject, attributeObject, $el) {
    if (
      $(this).documentController("getValue", "hub_icon_enum").value !== "IMAGE"
    ) {
      $el[0].style.display = "none";
    } else {
      $el[0].style.display = "inline";
    }
  }
);

window.dcp.document.documentController(
  "addEventListener",
  "attributeReady",
  {
    name: "icon:text:ready",
    documentCheck: documentObject => {
      return (
        documentObject.renderMode === "edit" ||
        documentObject.renderMode === "view"
      );
    },
    attributeCheck: attributeObject => {
      return attributeObject.id === "hub_icon_text";
    }
  },
  function(event, documentObject, attributeObject, $el) {
    if (
      $(this).documentController("getValue", "hub_icon_enum").value !== "HTML"
    ) {
      $el[0].style.display = "none";
    } else {
      $el[0].style.display = "inline";
    }
  }
);

window.dcp.document.documentController(
  "addEventListener",
  "attributeReady",
  {
    name: "icon:font:ready",
    documentCheck: documentObject => {
      return (
        documentObject.renderMode === "edit" ||
        documentObject.renderMode === "view"
      );
    },
    attributeCheck: attributeObject => {
      return attributeObject.id === "hub_icon_font";
    }
  },
  function(event, documentObject, attributeObject, $el) {
    if (
      $(this).documentController("getValue", "hub_icon_enum").value !== "FONT"
    ) {
      $el[0].style.display = "none";
    } else {
      $el[0].style.display = "inline";
    }
  }
);
