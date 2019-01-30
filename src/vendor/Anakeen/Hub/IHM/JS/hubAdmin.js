import Vue from "vue";
import LoadScript from "vue-m-loader";
import LoadCss from "load-css-file";

import ankHubAdmin from "../Components/HubAdmin/HubAdmin";
import AnkComponents from "@anakeen/ank-components";
import AnkAxios from "axios";

Vue.prototype.$http = AnkAxios.create();
Vue.use(AnkComponents);
Vue.use(LoadScript);
Vue.prototype.$loadCssFile = Vue.loadCssFile = LoadCss;

new Vue({
  el: "#ank-hub-admin",
  template: "<ank-hub-admin/>",
  components: {
    ankHubAdmin
  }
});
