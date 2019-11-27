import Vue from "vue";

import setup from "@anakeen/user-interfaces/components/lib/setup.esm";

import ankHubInstanciation from "../Components/HubAdminInstanciation/HubAdminInstanciation";

Vue.use(setup);

Vue.$_globalI18n.recordCatalog().then(() => {
  new Vue({
    el: "#ank-hub-instanciation",
    template: "<ank-hub-instanciation/>",
    components: {
      ankHubInstanciation
    }
  });
});
