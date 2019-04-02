import Vue from "vue";
import SearchUISEGrid from "./searchUISEGrid.vue";

new Vue({
  el: "#search-ui-se-grid",
  components: {
    "search-grid": SearchUISEGrid
  },
  template: "<search-grid></search-grid>"
});
