import Vue from "vue";
import TestPage from "./TestPage.vue";
import LoadScript from "vue-m-loader";

Vue.use(LoadScript);

new Vue({
  el: "#test",
  components: {
    TestPage
  },
  template: "<test-page></test-page>"
});
