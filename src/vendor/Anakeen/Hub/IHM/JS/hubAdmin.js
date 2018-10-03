import Vue from "vue";

import ankHubAdmin from "../Components/HubAdmin/HubAdmin";
import AnkComponents from "@anakeen/ank-components";
import AnkAxios from "axios";

Vue.prototype.$http = AnkAxios.create();
Vue.use(AnkComponents);

new Vue({
  el: "#ank-hub-admin",
  template: "<ank-hub-admin/>",
  components: {
    ankHubAdmin
  }
});
