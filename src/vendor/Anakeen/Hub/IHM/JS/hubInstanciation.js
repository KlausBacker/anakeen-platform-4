import Vue from "vue";

import Router from "../Router";
import ankHubInstanciation from "../Components/HubAdminInstanciation/HubAdminInstanciation";
import AnkComponents from "@anakeen/ank-components";
import AnkAxios from "axios";

Vue.prototype.$http = AnkAxios.create();
Vue.use(AnkComponents);

new Vue({
  el: "#ank-hub-instanciation",
  router: Router,
  template: "<ank-hub-instanciation/>",
  components: {
    ankHubInstanciation
  }
});
