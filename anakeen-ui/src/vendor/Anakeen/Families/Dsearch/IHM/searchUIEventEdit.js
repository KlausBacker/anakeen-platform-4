/**
 * Created by Alex on 09/06/15.
 */

/*
 Research result in edit mode
 */

const _ = require('underscore');
import showGrid from './searchUIGrid';

{

    window.dcp.document.documentController("addEventListener",
      "ready",
      {
          "name": "addDsearchResultEditEvent",
          "documentCheck": function isDsearch(document) {
              return document.renderMode === "edit" && (document.type === "search");
          }
      },
      function prepareResultEditEvents() {

          var $documentController = $(this);
          var $tab = $("#search-tabstrip");
          $tab.kendoTabStrip({
              select: function (event) {
                  var $li = $(event.item);
                  if ($li.hasClass("result-tab")) {
                      showTmpGrid(event, $documentController, $('.result--content'));
                  }
                  $li.parent().find("li.dcpLabel--active").removeClass("dcpLabel--active");
                  $li.addClass("dcpLabel--active");
              },
              show : function (event) {
                  // To update responsiveColumn
                $(window).trigger("resize");
              }
          });

          $(this).documentController("addEventListener",
            "actionClick",
            {
                "name": "previewEdit.editEvent",
                "documentCheck": function isDSearch(document) {
                    return ((document.type === "search") && document.renderMode === "edit");
                }
            },
            function eventButtonEdit(event, document, data) {
                if (data.eventId === "previewEdit") {
                    $tab.kendoTabStrip("select", ".result-tab");
                    //showGrid(event, $documentController);
                }
            }
          );
      });

    window.dcp.document.documentController("addEventListener",
      "close",
      {
          "name": "removeDsearchResultEditEvent",
          "documentCheck": function (document) {
              return (document.type === "search");
          }
      },
      function () {
          var $this = $(this);
          $this.documentController("removeEventListener", ".editEvent");
      }
    );

    window.dcp.document.documentController("addEventListener",
      "ready",
      {
          "name": "searchviewresults",
          "documentCheck": function (document) {
              return (document.type === "search");
          }
      },
      function viewresult(event, document) {

          var $result = $(".report-result-content");
          if ($result.length === 1) {
              showGrid(document.id, $result);
          }
      });

    function showTmpGrid(event, $documentController, $target) {
        var $dataJSON = null;
        var famid = $documentController.documentController("getValues").se_famid.value;

        $target.addClass("result--grid");
        $target.addClass("result--waiting");
        $dataJSON = $documentController.data("dcpDocumentController")._model.toJSON();
        $.ajax({
            method: "POST",
            url: "api/v1/search_UI_HTML5/temporaryDoc/" + $documentController.documentController("getProperties").family.name + "/",
            data: JSON.stringify($dataJSON),
            dataType: "json",
            contentType: 'application/json; charset=utf-8'
        }).done(function creation(docCreated) {
            var continueDefault = $documentController.documentController("triggerEvent", "custom:content",
              {
                  "familyName": $documentController.documentController("getProperties").family.name,
                  "id": docCreated.data.document.properties.id,
                  "title": $documentController.documentController("getProperties").title
              });
            if (!continueDefault) {
                event.preventDefault();
            }
            else {
                showGrid(docCreated.data.document.properties.id, $target).fail(function (errorMsg) {
                    $documentController.documentController("showMessage", {type: "error", message: errorMsg});

                });
            }
        }).fail(function failedCreation(jqXHR) {
            var response = JSON.parse(jqXHR.responseText);
            $documentController.documentController("showMessage", {type: "error", message: response.exceptionMessage});
        });

    }

}



