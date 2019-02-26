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

window.dcp.document.documentController(
  "addEventListener",
  "attributeReady",
  {
    name: "assetTypeAttributeSelect",
    documentCheck: documentObject => {
      return documentObject.renderMode === "edit";
    },
    attributeCheck: attribute => {
      return (
        attribute.id === "hub_instance_jsasset" ||
        attribute.id === "hub_instance_cssasset"
      );
    }
  },
  function assetsTypeSelect(event, doc, attribute, $el) {
    const $container = $el.closest(
      `tr[data-attrid=${attribute.id}s].dcpArray__content__line`
    );
    const assetType = $(
      `[data-attrid=${attribute.id}_type] [data-role=dropdownlist]`,
      $container
    ).data("kendoDropDownList");
    const kautocomplete = $(
      `.k-autocomplete [data-role=autocomplete]`,
      $el
    ).data("kendoAutoComplete");
    // Enable/Disable autocomplete following the asset type
    assetType.bind("select", event => {
      switch (event.dataItem.value) {
        case "manifest":
          // Enable autocomplete
          $el.find(".input-group-addon").show();
          kautocomplete.unbind("open");
          kautocomplete.options.suggest = true;
          break;
        case "path":
          // Disable autocomplete
          $el.find(".input-group-addon").hide();
          kautocomplete.options.suggest = false;
          kautocomplete.bind("open", function(e) {
            e.preventDefault();
          });
          break;
      }
    });
  }
);
