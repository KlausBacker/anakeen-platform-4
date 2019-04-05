import Vue from "vue";
import SearchViewGrid from "./searchViewGrid.vue";

new Vue({
  el: "#search-ui-view",
  components: {
    "search-ui-view": SearchViewGrid
  },
  template: "<search-ui-view></search-ui-view>"
});
