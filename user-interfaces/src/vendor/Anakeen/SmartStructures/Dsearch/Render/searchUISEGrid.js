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
        template: "<search-grid :searchId='searchId'></search-grid>"
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
