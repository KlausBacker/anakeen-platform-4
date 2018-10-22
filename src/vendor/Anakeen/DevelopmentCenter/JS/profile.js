import Vue from "vue";
import Axios from "axios";

import Profil from "../vue/components/profile/profile";

const axios = Axios.create();
Vue.prototype.$http = axios;

new Vue({
  el: "#profile-content",
  components: {
    "ank-dev-profile": Profil
  },
  template: `<ank-dev-profile v-bind:profileId="${
    window.profileId
  }"></ank-dev-profile>`
});
