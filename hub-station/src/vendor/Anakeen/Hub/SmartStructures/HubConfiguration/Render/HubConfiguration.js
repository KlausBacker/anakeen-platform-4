// JS asset for the edit render of hub configuration

import "./HubConfiguration.css";

window.ank.smartElement.globalController.registerFunction("hubConfiguration", function(controller) {
  controller.addEventListener(
    "smartFieldReady",
    {
      smartFieldCheck: smartField => smartField.id === "hub_docker_position"
    },
    function(event, sElement, sField, $el) {
      const selectedValue = sField.getValue().value;
      if (selectedValue) {
        $(".dock-area", $el).removeClass("dock-position-selected");
        $(`.dock-area[data-value=${selectedValue}]`, $el).addClass("dock-position-selected");
      }
      $(".dock-area", $el).on("click", event => {
        const clickValue = event.currentTarget.dataset.value;
        if (clickValue) {
          $(".dock-area", $el).removeClass("dock-position-selected");
          $(`.dock-area[data-value=${clickValue}]`, $el).addClass("dock-position-selected");
          this.setValue("hub_docker_position", {
            value: clickValue
          });
        }
      });
    }
  );

  controller.addEventListener(
    "smartFieldChange",
    {
      smartFieldCheck: smartField => smartField.id === "hub_activated"
    },
    (event, sElement, sField, values) => {
      const selectedValue = values.current.value;
      if (selectedValue && selectedValue === "TRUE") {
        $(event.target)
          .find(".dcpAttribute[data-attrid=hub_activated_order]")
          .show();
      } else {
        $(event.target)
          .find(".dcpAttribute[data-attrid=hub_activated_order]")
          .hide();
      }
    }
  );
});
