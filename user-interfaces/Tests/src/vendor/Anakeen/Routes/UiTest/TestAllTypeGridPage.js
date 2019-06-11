import Vue from "vue";
import TestAllPage from "./TestAllTypeGridPage.vue";
import LoadScript from "vue-m-loader";

Vue.use(LoadScript);

new Vue({
  el: "#test-all-page",
  components: {
    TestAllPage
  },
  template: "<test-all-page></test-all-page>"
});
