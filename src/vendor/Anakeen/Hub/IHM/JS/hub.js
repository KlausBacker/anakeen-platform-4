import Vue from "vue";

import VueAxiosPlugin from "@anakeen/internal-components/lib/AxiosPlugin";
import HubMain from "../Components/Hub/Hub.vue";
import Store from "../Components/HubStateManager";
import HubEntry from "../Components/Hub/utils/hubEntry";

Vue.use(VueAxiosPlugin);

const hubConf = new HubEntry(window.AnkHubInstanceId);

hubConf.fetchConfiguration().then(() => {
  hubConf.loadAssets().then(() => {
    Object.keys(window.ank.hub).map(currentKey => {
      Vue.component(currentKey, () => {
        return window.ank.hub[currentKey].promise;
      });
    });
    window.ank.hub.initialData = hubConf.data;
    new Vue({
      el: "#ank-hub",
      components: { "hub-main": HubMain },
      template: "<hub-main/>",
      store: Store
    });
  });
});
