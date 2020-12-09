window.ank.smartElement.globalController.registerFunction("mailTemplateRender", async scopedController => {
  function toogleHideShowCurrentUser(fieldToToogle, fieldValue, index) {
    if (fieldValue === "CU") {
      scopedController
        .$(`td[data-attrid=${fieldToToogle}]`)
        .eq(index)
        .hide();
      scopedController.setValue(fieldToToogle, { value: "", index });
    } else {
      scopedController
        .$(`td[data-attrid=${fieldToToogle}]`)
        .eq(index)
        .show();
    }
  }

  scopedController.addEventListener(
    "smartFieldChange",
    {
      smartFieldCheck: field => {
        return field.id === "tmail_fromtype";
      }
    },
    function toogleFromField(event, smartElement, smartField, values, index) {
      toogleHideShowCurrentUser("tmail_from", values.current[index].value, index);
    }
  );

  scopedController.addEventListener("ready", function toogleFromField() {
    const fromType = scopedController.getValue("tmail_fromtype");
    fromType.forEach((value, index) => {
      toogleHideShowCurrentUser("tmail_from", value.value, index);
    });

    const destType = scopedController.getValue("tmail_desttype");
    destType.forEach((value, index) => {
      toogleHideShowCurrentUser("tmail_recip", value.value, index);
    });
  });

  scopedController.addEventListener(
    "smartFieldChange",
    {
      smartFieldCheck: field => {
        return field.id === "tmail_desttype";
      }
    },
    function toogleFromField(event, smartElement, smartField, values, index) {
      toogleHideShowCurrentUser("tmail_recip", values.current[index].value, index);
    }
  );
});
