import Vue from "vue";

import ankHub from "../Components/Hub/Hub";
import AnkComponents from "@anakeen/ank-components";
import AnkAxios from "axios";

Vue.prototype.$http = AnkAxios.create();
Vue.use(AnkComponents);

new Vue({
  el: "#ank-hub",
  template: "<ank-hub/>",
  components: {
    ankHub
  }
});
