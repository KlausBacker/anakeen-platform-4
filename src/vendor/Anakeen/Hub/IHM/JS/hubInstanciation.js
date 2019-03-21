import Vue from "vue";

import ankHubInstanciation from "../Components/HubAdminInstanciation/HubAdminInstanciation";
import AnkAxios from "axios";

Vue.prototype.$http = AnkAxios.create();

new Vue({
  el: "#ank-hub-instanciation",
  template: "<ank-hub-instanciation/>",
  components: {
    ankHubInstanciation
  }
});
