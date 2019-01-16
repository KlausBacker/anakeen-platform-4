import "./HubConfiguration.css";
import axios from "axios";

axios.create();

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

window.dcp.document.documentController(
  "addEventListener",
  "attributeReady",
  {
    name: "icon:font:enum:ready",
    documentCheck: documentObject => {
      return documentObject.renderMode === "edit";
    },
    attributeCheck: attributeObject => {
      return attributeObject.id === "hub_icon_font";
    }
  },
  function(event, documentObject, attributeObject, $el) {
    axios.get("/hub/font/icons/").then(response => {
      $(".icon-picker").kendoDropDownList({
        select: e => {
          $(this).documentController("setValue", "hub_icon_font", {
            value: e.dataItem.value
          });
        }
      });
      const picker = $el.find("select").data("kendoDropDownList");
      picker.select(function(dataItem) {
        return (
          dataItem.value ===
          $(this).documentController("getValue", "hub_icon_font").value
        );
      });
      const options = picker.options;
      picker.setOptions(
        Object.assign({}, options, {
          template: "<i class='fa fa-#:value#'>#:value#</i>",
          valueTemplate: "<i class='fa fa-#:value#'></i>"
        })
      );
      picker.setDataSource(
        response.data.data.map(fa => {
          return {
            value: fa,
            displayValue: fa
          };
        })
      );
    });
  }
);

window.dcp.document.documentController(
  "addEventListener",
  "attributeReady",
  {
    name: "icon:font:picker:ready",
    documentCheck: documentObject => {
      return documentObject.renderMode === "view";
    },
    attributeCheck: attributeObject => {
      return attributeObject.id === "hub_icon_font";
    }
  },
  function(event, documentObject, attributeObject, $el) {
    if (
      $(this).documentController("getValue", "hub_icon_enum").value === "FONT"
    ) {
      $el.html(
        `<i class='fa fa-${
          $(this).documentController("getValue", "hub_icon_font").value
        }'></i>`
      );
    }
  }
);

window.dcp.document.documentController(
  "addEventListener",
  "attributeReady",
  {
    name: "dock_hub_positionReady",
    documentCheck: documentObject => {
      return documentObject.renderMode === "edit";
    },
    attributeCheck: attributeObject => {
      return attributeObject.id === "hub_docker_position";
    }
  },
  function(event, documentObject, attributeObject, $el) {
    const selectedValue = attributeObject.getValue().value;
    if (selectedValue) {
      $(".dock-area", $el).removeClass("dock-position-selected");
      $(`.dock-area[data-value=${selectedValue}]`, $el).addClass(
        "dock-position-selected"
      );
    }
    $(".dock-area", $el).on("click", event => {
      const clickValue = event.currentTarget.dataset.value;
      if (clickValue) {
        $(".dock-area", $el).removeClass("dock-position-selected");
        $(`.dock-area[data-value=${clickValue}]`, $el).addClass(
          "dock-position-selected"
        );
        $(this).documentController("setValue", "hub_docker_position", {
          value: clickValue
        });
      }
    });
  }
);
