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
      new Vue({
        el: ".search-ui-view",
        components: {
          "search-ui-view": SearchViewGrid
        },
        data: {
          controller: controller
        },
        template: "<search-ui-view :controller='controller'/>"
      });
    }
  );
}
