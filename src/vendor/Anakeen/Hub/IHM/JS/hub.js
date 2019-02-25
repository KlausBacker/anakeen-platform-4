import Vue from "vue";
import LoadScript from "vue-m-loader";
import LoadCss from "load-css-file";

import Router from "../Router";
import AnkComponents from "@anakeen/user-interfaces";
import { VueAxiosPlugin } from "@anakeen/internal-components";
import HubMain from "../Components/Hub/Hub.vue";

Vue.use(VueAxiosPlugin);
Vue.use(AnkComponents);
Vue.use(LoadScript);
Vue.prototype.$loadCssFile = Vue.loadCssFile = LoadCss;

new Vue({
  el: "#ank-hub",
  router: Router,
  components: {
    HubMain
  },
  template: "<hub-main></hub-main>"
});
