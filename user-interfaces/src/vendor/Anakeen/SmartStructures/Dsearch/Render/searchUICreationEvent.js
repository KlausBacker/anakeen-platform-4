/*
Ask for a document title in creation mode
 */

export default function searchUICreationEventProcess(controller) {
  controller.addEventListener(
    "ready",
    {
      name: "addDsearchCreationEvent",
      check: function(document) {
        return document.type === "search";
      }
    },
    function prepareResultViewEvents() {
      /*
              add a pop-up window to define a name to the new document
               */
      controller.addEventListener(
        "actionClick",
        {
          name: "confirmCreation.createEvent",
          check: function isDSearch(document) {
            return document.type === "search" && document.viewId === "!coreCreation";
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
            var windowContent = $(".dcpCustomTemplate--content[data-attrid='ba_title']");
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
              controller.saveSmartElement();
              dialog.close();
            });
          }
        }
      );

      controller.addEventListener(
        "actionClick",
        {
          name: "confirmCreation&Close.createEvent",
          check: function isDSearch(document) {
            return document.type === "search" && document.viewId === "!coreCreation";
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
            var windowContent = $(".dcpCustomTemplate--content[data-attrid='ba_title']");
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
              controller.saveSmartElement();
              dialog.close();
              controller.addEventListener(
                "afterSave",
                {
                  name: "switchToViewAfterSave.createEvent"
                },
                function reloadInConsultation(event, document) {
                  controller.fetchSmartElement({
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

  controller.addEventListener(
    "close",
    {
      name: "removeDsearchCreateEvent",
      check: function(document) {
        return document.type === "search";
      }
    },
    function() {
      controller.removeEventListener(".createEvent");
    }
  );
}
