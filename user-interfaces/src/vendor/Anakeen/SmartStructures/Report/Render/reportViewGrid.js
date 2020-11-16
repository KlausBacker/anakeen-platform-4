import Vue from "vue";
import SearchViewGrid from "../../Dsearch/Render/searchViewGrid.vue";
import $ from "jquery";

export default function reportViewGridProcess(controller) {
  controller.addEventListener(
    "ready",
    {
      name: "report:view:grid",
      check: document => {
        const serverData = document.controller.getCustomServerData();
        if (serverData["SEName"]) {
          return document.renderMode === "view" && serverData["SEName"].indexOf("REPORT") >= 0;
        }
      }
    },
    evt => {
      const SearchViewGridComponent = Vue.extend(SearchViewGrid);
      const searchGrid = new SearchViewGridComponent({
        el: $(evt.target).find(".search-ui-view")[0]
      });
      /*
       * Propagate actionClick event on the searchRowActionClick event for exemple to display SE in new AnkSeTab in BusinessApp
       */
      searchGrid.$on("searchRowActionClick", gridEvent => {
        controller
          .triggerEvent("actionClick", controller.getProperties(), {
            target: gridEvent.target,
            originalEvent: gridEvent,
            eventId: "document.load",
            /*
             * Hack for businessApp: tabId is computed from the first option identifier
             * So we pass the id of the smart element to display every smart element in different tab (initid is the same between every SE revision)
             */
            options: [gridEvent.data.row.properties.id, "!defaultConsultation", gridEvent.data.row.properties.revision]
          })
          .catch(() => {
            // event prevented
            gridEvent.preventDefault();
          });
      });
      controller.addEventListener("actionClick", (event, smartElementProps, data) => {
        if (data.eventId === "exportReport") {
          searchGrid.showButton();
          searchGrid.export();
        }
      });
    }
  );
}
