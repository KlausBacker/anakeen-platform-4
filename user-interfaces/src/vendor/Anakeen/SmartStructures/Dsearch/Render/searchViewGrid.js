import Vue from "vue";
import SearchViewGrid from "./searchViewGrid.vue";

import setup from "@anakeen/user-interfaces/components/lib/setup.esm";
Vue.use(setup);

new Vue({
  el: ".search-ui-view",
  components: {
    "search-ui-view": SearchViewGrid
  },
  template: "<search-ui-view></search-ui-view>"
});
