import Vue from "vue";
import LoadScript from "vue-m-loader";
import LoadCss from "load-css-file";

import Router from "../Router";
import AnkComponents from "@anakeen/ank-components";
import AnkAxios from "axios";
import HubMain from "../Components/Hub/Hub.vue";
import HelloWorld from "../Components/HelloWorld";
Vue.use(HelloWorld);

Vue.prototype.$http = AnkAxios.create();
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
