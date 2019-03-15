import Vue from "vue";
import LoadScript from "vue-m-loader";
import LoadCss from "load-css-file";

import AnkComponents from "@anakeen/user-interfaces";
import VueAxiosPlugin from "@anakeen/internal-components/lib/AxiosPlugin";
import HubMain from "../Components/Hub/Hub.vue";
import Store from "../Components/HubStateManager";

Vue.use(VueAxiosPlugin);
Vue.use(AnkComponents);
Vue.use(LoadScript);

Vue.prototype.$loadCssFile = Vue.loadCssFile = LoadCss;

new Vue({
  el: "#ank-hub",
  components: {
    HubMain
  },
  template: "<hub-main></hub-main>",
  store: Store
});
