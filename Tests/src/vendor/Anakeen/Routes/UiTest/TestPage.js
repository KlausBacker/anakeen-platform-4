import Vue from "vue";
import TestPage from "./TestPage.vue";

new Vue({
  el: "#test",
  components: {
    TestPage
  },
  template: "<test-page></test-page>"
});
