import "./RenderDescriptionEdit.scss";
import AnkInitGlobalController from "@anakeen/user-interfaces/components/lib/AnkInitController.esm";
import $ from "jquery";

export default AnkInitGlobalController.then(globalController => {
  globalController.registerFunction("renderDescriptionEdit", controller => {
    controller.addEventListener(
      "smartFieldReady",
      {
        smartFieldCheck: function isTitle(smartField) {
          return smartField.id === "rd_fieldlabel";
        }
      },
      function(event, smartElement, smartField, $el, index) {
        $el
          .closest(".rd-field-description")
          .find(".rd-field-label")
          .text(smartField.getValue()[index].value);
      }
    );

    controller.addEventListener(
      "smartFieldChange",
      {
        smartFieldCheck: function isTitle(smartField) {
          return smartField.id === "rd_fieldlabel";
        }
      },
      function(event, smartElement, smartField, value, index) {
        if (index >= 0) {
          const $fields = $(event.target.find(".rd-field-label")[index]);

          $fields.text(value.current[index].value);
        }
      }
    );

    /**
     * Add buttons to collapse/expand description in array
     */
    controller.addEventListener(
      "smartFieldReady",
      {
        name: "addExpandButton",
        smartFieldCheck: function isTitle(smartField) {
          return smartField.id === "rd_t_fields";
        }
      },
      function changeDisplayError(event, smartElement, smartField, $el) {
        const $button = $('<button type="button" class="btn btn-default"><i class="fa fa-angle-double-up"/></button>');
        const $label = $el.find(".dcpArray__label");
        $label.append($button);

        const collapseFt = ($button, collapse) => {
          const $fa = $button.find(".fa");
          const $desc = $button.closest(".rd-field-description").find(".rd-descriptions, .rd-field-label");
          if (!collapse) {
            $button.data("isCollapsed", false);
            $fa.removeClass("fa-angle-double-down");
            $fa.addClass("fa-angle-double-up");
            $desc.show();
          } else {
            $button.data("isCollapsed", true);
            $fa.addClass("fa-angle-double-down");
            $fa.removeClass("fa-angle-double-up");
            $desc.hide();
          }
        };

        $el.on("click", ".rd-vertical > button", e => {
          const $target = $(e.currentTarget);
          const isCollapsed = $target.data("isCollapsed") === true;

          collapseFt($target, !isCollapsed);
        });

        $button.on("click", e => {
          const $target = $(e.currentTarget);
          const $buttons = $el.find(".rd-vertical > button");

          const isCollapsed = $target.data("isCollapsed") === true;
          collapseFt($target, !isCollapsed);
          e.stopPropagation();
          $buttons.each(function() {
            collapseFt($(this), !isCollapsed);
          });
        });
      }
    );
  });
});
