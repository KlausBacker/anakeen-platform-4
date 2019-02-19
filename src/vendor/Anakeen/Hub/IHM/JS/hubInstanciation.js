import Vue from "vue";

import ankHubInstanciation from "../Components/HubAdminInstanciation/HubAdminInstanciation";
import AnkComponents from "@anakeen/user-interfaces";
import AnkAxios from "axios";

Vue.prototype.$http = AnkAxios.create();
Vue.use(AnkComponents);

new Vue({
  el: "#ank-hub-instanciation",
  template: "<ank-hub-instanciation/>",
  components: {
    ankHubInstanciation
  }
});
