import Vue from "vue";
import TestGridPage from "./TestGridPage.vue";
import LoadScript from "vue-m-loader";

Vue.use(LoadScript);

new Vue({
  el: "#test-grid-page",
  components: {
    TestGridPage
  },
  template: "<test-grid-page></test-grid-page>"
});
