import $ from "jquery";

/*
Research result in consult mode
 */

export default function searchUIEventViewProcess(controller) {
  controller.addEventListener(
    "ready",
    {
      name: "addDsearchResultViewEvent",
      check: function(document) {
        return document.type === "search";
      }
    },
    function prepareResultViewEvents() {
      controller.addEventListener(
        "actionClick",
        {
          name: "previewConsult.viewEvent",
          check: function isDSearch(document) {
            return document.type === "search" && document.renderMode === "view";
          }
        },
        function eventButtonView(event, document, data) {
          if (data.eventId === "previewConsult") {
            var continueDefault = controller.triggerEvent("custom:content", {
              familyName: controller.getProperties().family.name,
              id: controller.getProperties().id,
              title: controller.getProperties().title
            });
            if (!continueDefault) {
              event.preventDefault();
            } else {
              var $window = $("<div />");
              $("body").append($window);
              $window.kendoWindow({
                title: controller.getProperties().title,
                content: {
                  url: `/api/v2/smartstructures/dsearch/preview/${controller.getProperties().id.toString()}`
                },
                iframe: true,
                position: {
                  top: 0,
                  left: 0
                },
                open: function openWindow(event) {
                  event.sender.wrapper.addClass("dsearch-result-window");
                },
                pinned: false,
                width: "90%",
                height: "90%",
                actions: ["Minimize", "Maximize", "Close"]
              });
              $window.kendoWindow("center");
            }
          }
        }
      );
    }
  );

  controller.addEventListener(
    "close",
    {
      name: "removeDsearchResultViewEvent",
      check: function(document) {
        return document.type === "search";
      }
    },
    function() {
      controller.removeEventListener(".viewEvent");
    }
  );
}
