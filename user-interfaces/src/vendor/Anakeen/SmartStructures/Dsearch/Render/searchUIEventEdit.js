/*
 Research result in edit mode
 */
export default function searchUIEventEditProcess(controller) {
  controller.addEventListener(
    "ready",
    {
      name: "addDsearchResultEditEvent",
      check: function isDsearch(document) {
        return document.renderMode === "edit" && document.type === "search";
      }
    },
    function prepareResultEditEvents() {
      var $tab = $("#search-tabstrip");
      $tab.kendoTabStrip({
        select: function(event) {
          var $li = $(event.item);
          if ($li.hasClass("result-tab")) {
            showTmpGrid(event);
          }
          $li
            .parent()
            .find("li.dcpLabel--active")
            .removeClass("dcpLabel--active");
          $li.addClass("dcpLabel--active");
        },
        show: function() {
          // To update responsiveColumn
          $(window).trigger("resize");
        }
      });
    }
  );

  controller.addEventListener(
    "close",
    {
      name: "removeDsearchResultEditEvent",
      check: function(document) {
        return document.type === "search";
      }
    },
    function() {
      controller.removeEventListener(".editEvent");
    }
  );

  function showTmpGrid(event) {
    const $dataJSON = controller._model.toJSON();
    $dataJSON.document.attributes = controller._model.getValues(false);
    $.ajax({
      method: "POST",
      url: "/api/v2/smartstructures/dsearch/temporaryDoc/" + controller.getProperties().family.name + "/",
      data: JSON.stringify($dataJSON),
      dataType: "json",
      contentType: "application/json; charset=utf-8"
    })
      .done(function creation(docCreated) {
        var continueDefault = controller.triggerEvent("custom:content", {
          familyName: controller.getProperties().family.name,
          id: docCreated.data.document.properties.id,
          title: controller.getProperties().title
        });
        if (!continueDefault) {
          event.preventDefault();
        }
      })
      .fail(function failedCreation(jqXHR) {
        var response = JSON.parse(jqXHR.responseText);
        controller.showMessage({
          type: "error",
          message: response.exceptionMessage
        });
      });
  }
}
