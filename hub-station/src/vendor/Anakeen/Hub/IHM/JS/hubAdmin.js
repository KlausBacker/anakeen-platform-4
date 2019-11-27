import Vue from "vue";

import setup from "@anakeen/user-interfaces/components/lib/setup.esm";
import ankHubAdmin from "../Components/HubAdmin/HubAdmin";

Vue.use(setup);

Vue.$_globalI18n.recordCatalog().then(() => {
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
});
