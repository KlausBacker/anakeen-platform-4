import $ from "jquery";

/*
Research result in consult mode
 */

export default function searchUIEventViewProcess(controller) {
  controller.addEventListener(
    "actionClick",
    {
      name: "previewConsult.viewEvent",
      check: function isDSearch(document) {
        const serverData = document.controller.getCustomServerData();
        if (serverData["SEName"]) {
          return document.renderMode === "view" && serverData["SEName"].indexOf("DSEARCH") >= 0;
        }
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
