import "./HubConfiguration.css";

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
