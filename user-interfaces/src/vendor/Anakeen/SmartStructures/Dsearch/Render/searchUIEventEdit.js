/*
 Research result in edit mode
 */
{
  window.dcp.document.documentController(
    "addEventListener",
    "ready",
    {
      name: "addDsearchResultEditEvent",
      documentCheck: function isDsearch(document) {
        return document.renderMode === "edit" && document.type === "search";
      }
    },
    function prepareResultEditEvents() {
      var $documentController = $(this);
      var $tab = $("#search-tabstrip");
      $tab.kendoTabStrip({
        select: function(event) {
          var $li = $(event.item);
          if ($li.hasClass("result-tab")) {
            showTmpGrid(event, $documentController);
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

  window.dcp.document.documentController(
    "addEventListener",
    "close",
    {
      name: "removeDsearchResultEditEvent",
      documentCheck: function(document) {
        return document.type === "search";
      }
    },
    function() {
      var $this = $(this);
      $this.documentController("removeEventListener", ".editEvent");
    }
  );

  function showTmpGrid(event, $documentController) {
    const $dataJSON = $documentController
      .data("dcpDocumentController")
      ._model.toJSON();
    $dataJSON.document.attributes = $documentController
      .data("dcpDocumentController")
      ._model.getValues(false);
    $.ajax({
      method: "POST",
      url:
        "/api/v2/smartstructures/dsearch/temporaryDoc/" +
        $documentController.documentController("getProperties").family.name +
        "/",
      data: JSON.stringify($dataJSON),
      dataType: "json",
      contentType: "application/json; charset=utf-8"
    })
      .done(function creation(docCreated) {
        var continueDefault = $documentController.documentController(
          "triggerEvent",
          "custom:content",
          {
            familyName: $documentController.documentController("getProperties")
              .family.name,
            id: docCreated.data.document.properties.id,
            title: $documentController.documentController("getProperties").title
          }
        );
        if (!continueDefault) {
          event.preventDefault();
        }
      })
      .fail(function failedCreation(jqXHR) {
        var response = JSON.parse(jqXHR.responseText);
        $documentController.documentController("showMessage", {
          type: "error",
          message: response.exceptionMessage
        });
      });
  }
}