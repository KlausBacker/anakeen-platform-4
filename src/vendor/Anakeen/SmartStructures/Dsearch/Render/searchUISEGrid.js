import Vue from "vue";
import SearchUISEGrid from "./searchUISEGrid.vue";

window.dcp.document.documentController(
  "addEventListener",
  "ready",
  {
    name: "seGrid:ready",
    documentCheck: function isDsearch(document) {
      return document.renderMode === "edit" && document.type === "search";
    }
  },
  () => {
    new Vue({
      el: ".search-ui-se-grid",
      components: {
        "search-grid": SearchUISEGrid
      },
      template: "<search-grid></search-grid>"
    });
  }
);
