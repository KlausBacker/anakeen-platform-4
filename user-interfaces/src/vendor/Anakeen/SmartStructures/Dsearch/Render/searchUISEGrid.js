import Vue from "vue";
import SearchUISEGrid from "./searchUISEGrid.vue";
export default function searchUISEGridProcess(controller) {
  controller.addEventListener(
    "ready",
    {
      name: "seGrid:ready",
      check: function isDsearch(document) {
        return document.renderMode === "edit" && document.type === "search";
      }
    },
    () => {
      const searchVueGrid = new Vue({
        components: {
          "search-grid": SearchUISEGrid
        },
        el: ".search-ui-se-grid",
        data: { searchId: null },
        template:
          "<search-grid :searchId='searchId' @searchGridError='onSearchGridError' @searchRowActionClick='onSearchGridRowActionClick'></search-grid>",
        methods: {
          onSearchGridError(event) {
            event.forEach(err => {
              controller.showMessage({
                type: "error",
                message: err.data.message
              });
            });
          },
          /*
           * Propagate actionClick event on the searchRowActionClick event for exemple to display SE in new AnkSeTab in BusinessApp
           */
          onSearchGridRowActionClick(gridEvent) {
            controller
              .triggerEvent("actionClick", controller.getProperties(), {
                target: gridEvent.target,
                originalEvent: gridEvent,
                eventId: "document.load",
                /*
                 * Hack for businessApp: tabId is computed from the first option identifier
                 * So we pass the id of the smart element to display every smart element in different tab (initid is the same between every SE revision)
                 */
                options: [
                  gridEvent.data.row.properties.id,
                  "!defaultConsultation",
                  gridEvent.data.row.properties.revision
                ]
              })
              .catch(() => {
                // event prevented
                gridEvent.preventDefault();
              });
          }
        }
      });
      controller.addEventListener(
        "custom:content",
        {
          name: "getTmpSearchId",
          check: function isDsearch(document) {
            return document.type === "search";
          }
        },
        function prepareResultEditEvents(event, data) {
          searchVueGrid.searchId = data.id.toString();
        }
      );
      controller.addEventListener(
        "custom:content:view",
        {
          name: "getTmpViewId",
          check: function isReport(document) {
            return document.type === "search";
          }
        },
        function prepareResultViewEvents(event, data) {
          searchVueGrid.searchId = data.id.toString();
        }
      );
    }
  );
}
