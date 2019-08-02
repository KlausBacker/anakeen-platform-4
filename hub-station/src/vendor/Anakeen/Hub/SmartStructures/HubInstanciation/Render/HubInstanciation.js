window.ank.smartElement.globalController.registerFunction("hubInstanciationRender", controller => {
  controller.addEventListener(
    "smartFieldReady",
    {
      smartFieldCheck: attribute => attribute.id === "hub_instance_jsasset" || attribute.id === "hub_instance_cssasset"
    },
    (event, sElement, smartField, $el) => {
      const $container = $el.closest(`tr[data-attrid=${smartField.id}s].dcpArray__content__line`);
      const assetType = $(`[data-attrid=${smartField.id}_type] [data-role=dropdownlist]`, $container).data(
        "kendoDropDownList"
      );
      const kautocomplete = $(`.k-autocomplete [data-role=autocomplete]`, $el).data("kendoAutoComplete");
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
});
