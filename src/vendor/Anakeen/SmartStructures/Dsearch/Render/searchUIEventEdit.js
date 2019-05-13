/*
 Research result in edit mode
 */
{
  var docFamid = window.dcp.document.documentController("getValue", "se_famid")
    .value;
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

      $(this).documentController(
        "addEventListener",
        "actionClick",
        {
          name: "previewEdit.editEvent",
          documentCheck: function isDSearch(document) {
            return document.type === "search" && document.renderMode === "edit";
          }
        },
        function eventButtonEdit(event, document, data) {
          if (data.eventId === "previewEdit") {
            var currentTab = $tab.data("kendoTabStrip").select()[0].className;
            if (currentTab.includes("result-tab")) {
              var newFamid = window.dcp.document.documentController(
                "getValue",
                "se_famid"
              ).value;
              if (docFamid !== newFamid) {
                showTmpGrid(event, $documentController);
                docFamid = newFamid;
              } else {
                showViewGrid(event, $documentController);
              }
            } else {
              $tab.kendoTabStrip("select", ".result-tab");
            }
          }
        }
      );
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

  window.dcp.document.documentController(
    "addEventListener",
    "ready",
    {
      name: "searchviewresults",
      documentCheck: function(document) {
        return document.type === "search";
      }
    },
    function viewresult(event) {
      var $documentController = $(this);
      showViewGrid(event, $documentController);
    }
  );

  function showViewGrid(event, $documentController) {
    var continueDefault = $documentController.documentController(
      "triggerEvent",
      "custom:content:view",
      {
        familyName: $documentController.documentController("getProperties")
          .family.name,
        id: $documentController.documentController("getProperties").id,
        title: $documentController.documentController("getProperties").title
      }
    );
    if (!continueDefault) {
      event.preventDefault();
    }
  }

  function showTmpGrid(event, $documentController) {
    var $dataJSON = null;
    $dataJSON = $documentController
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
