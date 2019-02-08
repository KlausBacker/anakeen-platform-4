import Vue from "vue";

import AdminCenterParameters from "./AdminCenterParameters";
import "@progress/kendo-ui/js/kendo.treelist";
import "@progress/kendo-ui/js/kendo.window";
import "@progress/kendo-ui/js/kendo.button";
import "@progress/kendo-ui/js/kendo.grid";

new Vue({
  el: "#ank-admin-parameter",
  components: {
    AdminCenterParameters
  },
  template: "<ank-admin-parameter></ank-admin-parameter>"
});
