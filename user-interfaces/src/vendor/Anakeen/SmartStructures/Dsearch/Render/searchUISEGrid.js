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
      new Vue({
        el: ".search-ui-se-grid",
        components: {
          "search-grid": SearchUISEGrid
        },
        data: { controller: controller },
        template: "<search-grid :controller='controller'></search-grid>"
      });
    }
  );
}
