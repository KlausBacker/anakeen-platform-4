import "./HubBusinessApp.css";

function displayIconField(element, iconType) {
  const $el = $(element);
  switch (iconType) {
    case "ICON":
      $el.find(".dcpAttribute[data-attrid=hba_icon_lib]").show();
      $el.find(".dcpAttribute[data-attrid=hba_icon_html]").hide();
      $el.find(".dcpAttribute[data-attrid=hba_icon_image]").hide();
      prepareIconLibSelector.call(this, $el.find(".dcpAttribute[data-attrid=hba_icon_lib]"));
      break;
    case "HTML":
      $el.find(".dcpAttribute[data-attrid=hba_icon_lib]").hide();
      $el.find(".dcpAttribute[data-attrid=hba_icon_html]").show();
      $el.find(".dcpAttribute[data-attrid=hba_icon_image]").hide();
      break;
    case "IMAGE":
      $el.find(".dcpAttribute[data-attrid=hba_icon_lib]").hide();
      $el.find(".dcpAttribute[data-attrid=hba_icon_html]").hide();
      $el.find(".dcpAttribute[data-attrid=hba_icon_image]").show();
      break;
  }
}

function prepareIconLibSelector($el) {
  $($el.find(".icon-selector[data-attrid=hba_icon_lib]")).kendoDropDownList({
    select: (e) => {
      const selectedValue = e.dataItem.value;
      $(this).documentController("setValue", "hba_icon_lib", {
        value: "<i class='fa fa-"+selectedValue+"'></i>"
      });
    },
    template: "<span style='display: flex; align-items: center'><i style='margin-right: .5rem' class='fa fa-#:value#'></i> <span>#:value#</span></span>",
    valueTemplate: "<span style='display: flex; align-items: center'><i style='margin-right: .5rem' class='fa fa-#:value#'></i> <span>#:value#</span></span> "
  });
}

window.dcp.document.documentController(
  "addEventListener",
  "change",
  {
    attributeCheck: (attribute) => attribute.id === "hba_icon_type",
    documentCheck: (document) => document.renderMode === "edit",
    name: "onHbaIconTypeChange"
  },
  function onHbaIconTypeChange(event, element, attribute, values) {
    displayIconField.call(this, event.target, values.current.value);
  }
);

window.dcp.document.documentController(
  "addEventListener",
  "attributeReady",
  {
    attributeCheck: (attr) => attr.id === "hba_icon_html" || attr.id === "hba_icon_lib" || attr.id === "hba_icon_image",
    documentCheck: (document) => document.renderMode === "edit",
    name: "onHbaIconTypeInit"
  },
  function onHbaIconTypeInit(event, element, attribute, $el) {
    const iconType = $(this).documentController("getValue", "hba_icon_type").value;
    switch (iconType) {
      case "ICON":
        if (attribute.id === "hba_icon_lib") {
          prepareIconLibSelector.call(this, $el);
          $el.show();
        } else {
          $el.hide();
        }
        break;
      case "HTML":
        if (attribute.id === "hba_icon_html") {
          $el.show();
        } else {
          $el.hide();
        }
        break;
      case "IMAGE":
        if (attribute.id === "hba_icon_image") {
          $el.show();
        } else {
          $el.hide();
        }
        break;
    }
  }
);

window.dcp.document.documentController(
  "addEventListener",
  "attributeCreateDialogDocumentReady",
  {
    documentCheck: (document) => document.renderMode === "edit",
    name: "onCreateDialogDocumentReady"
  },
  function onCreateDialogDocumentReady(event, document, attr, options) {
    console.log($(event.target).find(".k-window"), event.target);
  }
);