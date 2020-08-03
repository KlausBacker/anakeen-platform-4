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
        console.log(smartField.getLabel());
        $el
          .closest(".rd-field-description")
          .find(".rd-field-label")
          .text(smartField.getValue()[index].value);
        console.log($el);
      }
    );

    controller.addEventListener(
      "smartFieldChange",
      {
        smartFieldCheck: function isTitle(smartField) {
          console.log(smartField.id);
          return smartField.id === "rd_fieldlabel";
        }
      },
      function(event, smartElement, smartField, value, index) {

        const $fields=$(event.target.find('.rd-field-label')[index]);

        $fields.text(value.current[index].value);
        //data-attrid="rd_t_fields" data-line="0
        console.log($fields);



      }
    );

  });
});
