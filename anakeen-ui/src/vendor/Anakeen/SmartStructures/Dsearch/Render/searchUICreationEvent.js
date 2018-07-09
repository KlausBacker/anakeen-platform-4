/**
 * Created by Alex on 06/07/15.
 */

/*
Ask for a document title in creation mode
 */

const _ = require("underscore");

{
  window.dcp.document.documentController(
    "addEventListener",
    "ready",
    {
      name: "addDsearchCreationEvent",
      documentCheck: function(document) {
        return document.type === "search";
      }
    },
    function prepareResultViewEvents() {
      /*
            add a pop-up window to define a name to the new document
             */
      $(this).documentController(
        "addEventListener",
        "actionClick",
        {
          name: "confirmCreation.createEvent",
          documentCheck: function isDSearch(document) {
            return (
              document.type === "search" && document.viewId === "!coreCreation"
            );
          }
        },
        function eventButtonView(event, document, data) {
          if (data.eventId === "confirmCreation") {
            var $window = $('<div class="windowConfirm"/>');
            $("body").append($window);
            $window.kendoWindow({
              width: "300px",
              height: "85px",
              position: {
                top: "20%",
                left: "30%"
              },
              resizable: false,
              modal: true,
              title: i18n.___("type a title", "SEvents"),
              close: function() {
                $(".closeBtn").remove();
              }
            });
            var windowContent = $(
              ".dcpCustomTemplate--content[data-attrid='ba_title']"
            );
            windowContent.show();
            var nameTitle = $(".dcpAttribute__left[data-attrid='ba_title']");
            nameTitle.hide();
            var nameInput = $(".dcpAttribute__right[data-attrid='ba_title']");
            nameInput.find("input").css("height", "32px");
            nameInput.css("width", "98%");

            var button = $('<button class="closeBtn">Ok</button>');
            button.css("margin-left", "88%");
            button.css("margin-top", "2%");
            var dialog = $($window).data("kendoWindow");
            dialog.content(windowContent.append(button));

            button.click(function clickOnCreateButton() {
              window.dcp.document.documentController("saveDocument");
              dialog.close();
            });
          }
        }
      );

      $(this).documentController(
        "addEventListener",
        "actionClick",
        {
          name: "confirmCreation&Close.createEvent",
          documentCheck: function isDSearch(document) {
            return (
              document.type === "search" && document.viewId === "!coreCreation"
            );
          }
        },
        function eventButtonView(event, document, data) {
          if (data.eventId === "confirmCreationClose") {
            var $window = $('<div class="windowConfirm"/>');
            $("body").append($window);
            $window.kendoWindow({
              width: "300px",
              height: "85px",
              position: {
                top: "20%",
                left: "30%"
              },
              resizable: false,
              modal: true,
              title: i18n.___("type a title", "SEvents"),
              close: function() {
                $(".closeBtn").remove();
              }
            });
            var windowContent = $(
              ".dcpCustomTemplate--content[data-attrid='ba_title']"
            );
            windowContent.show();
            var nameTitle = $(".dcpAttribute__left[data-attrid='ba_title']");
            nameTitle.hide();
            var nameInput = $(".dcpAttribute__right[data-attrid='ba_title']");
            nameInput.find("input").css("height", "32px");
            nameInput.css("width", "98%");

            var button = $('<button class="closeBtn">Ok</button>');
            button.css("margin-left", "88%");
            button.css("margin-top", "2%");
            var dialog = $($window).data("kendoWindow");
            dialog.content(windowContent.append(button));

            button.click(function clickOnCreateButton() {
              window.dcp.document.documentController("saveDocument");
              dialog.close();
              window.dcp.document.documentController(
                "addEventListener",
                "afterSave",
                {
                  name: "switchToViewAfterSave.createEvent"
                },
                function reloadInConsultation(event, document) {
                  window.dcp.document.documentController("fetchDocument", {
                    initid: document.id,
                    revision: -1,
                    viewId: "!defaultConsultation"
                  });
                }
              );
            });
          }
        }
      );
    }
  );

  window.dcp.document.documentController(
    "addEventListener",
    "close",
    {
      name: "removeDsearchCreateEvent",
      documentCheck: function(document) {
        return document.type === "search";
      }
    },
    function() {
      var $this = $(this);
      $this.documentController("removeEventListener", ".createEvent");
    }
  );
}
