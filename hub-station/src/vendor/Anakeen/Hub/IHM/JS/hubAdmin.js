import Vue from "vue";

import ankHubAdmin from "../Components/HubAdmin/HubAdmin";
import AnkAxios from "axios";

Vue.prototype.$http = AnkAxios.create();

new Vue({
  el: "#ank-hub-admin",
  template: "<ank-hub-admin :hub-id='hubId'/>",
  components: {
    ankHubAdmin
  },
  data: {
    hubId: 0
  },
  beforeMount() {
    this.hubId = this.$el.dataset.hubid;
  }
});
