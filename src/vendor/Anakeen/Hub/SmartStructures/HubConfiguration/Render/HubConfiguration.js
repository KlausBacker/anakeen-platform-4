import "./HubConfiguration.css";
require("@fonticonpicker/fonticonpicker")($);
import "@fonticonpicker/fonticonpicker/dist/css/base/jquery.fonticonpicker.min.css";
import "@fonticonpicker/fonticonpicker/dist/css/themes/bootstrap-theme/jquery.fonticonpicker.bootstrap.min.css";

window.dcp.document.documentController(
  "addEventListener",
  "attributeReady",
  {
    name: "hub:configuration:view:ready",
    documentCheck: documentObject => {
      return documentObject.renderMode === "view";
    },
    attributeCheck: attributeObject => {
      return attributeObject.id === "hub_icon";
    }
  },
  (event, documentObject, attributeObject, $el) => {
    $el[0].lastElementChild.firstElementChild.innerHTML =
      $el[0].lastElementChild.firstElementChild.textContent;
  }
);

window.dcp.document.documentController(
  "addEventListener",
  "attributeReady",
  {
    name: "hub:configuration:edit:ready",
    documentCheck: documentObject => {
      return documentObject.renderMode === "edit";
    },
    attributeCheck: attributeObject => {
      return attributeObject.id === "hub_icon";
    }
  },
  function(event, documentObject, attributeObject, $el) {
    $el.find(".iconPicker").fontIconPicker({
      theme: "fip-bootstrap",
      useAttribute: true,
      iconGenerator: icon => {
        return `<i class="fa fa-${icon}"></i>`;
      },
      convertToHex: false
    });
    $el.find(".iconPicker").on("change", () => {});
  }
);
