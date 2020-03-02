import Vue from "vue";
import SearchViewGrid from "../../Dsearch/Render/searchViewGrid.vue";

export default function reportViewGridProcess(controller) {
  controller.addEventListener(
    "ready",
    {
      name: "report:view:grid",
      check: document => {
        return document.family.name === "REPORT" && document.renderMode === "view" && document.type === "search";
      }
    },
    () => {
      const SearchViewGridComponent = Vue.extend(SearchViewGrid);
      const searchGrid = new SearchViewGridComponent({
        el: ".search-ui-view"
      });
      controller.addEventListener("actionClick", (event, smartElementProps, data) => {
        if (data.eventId === "exportReport") {
          if (navigator.online) {
            searchGrid.showButton();
            searchGrid.export();
          }
        }
      });
    }
  );
}
