import Vue from "vue";
import $ from "jquery";

import setup from "@anakeen/user-interfaces/components/lib/setup.esm";
import HubMain from "../Components/Hub/Hub.vue";
import HubLoading from "../Components/Hub/HubComponentStatus/HubComponentLoading.vue";
import HubError from "../Components/Hub/HubComponentStatus/HubComponentError.vue";
import Store from "../Components/HubStateManager";
import HubEntry from "@anakeen/hub-components/lib/HubEntriesUtil";

//Share vue between all hub elements
window.vue = Vue;

Vue.use(setup);

const hubConf = new HubEntry(window.AnkHubInstanceId);

const enableLoader = (enable = true, element = "body") => {
  kendo.ui.progress($(element), enable);
};

enableLoader();

Vue.$_globalI18n.recordCatalog().then(() => {
  hubConf
    .fetchConfiguration()
    .then(() => {
      hubConf.loadAssets().then(() => {
        Object.keys(window.ank.hub).map(currentKey => {
          Vue.component(currentKey, () => {
            const componentConfig = {
              component: window.ank.hub[currentKey].promise,
              loading: HubLoading,
              error: HubError,
              delay: 100
            };
            if (window.ank.hub[currentKey].timeout) {
              componentConfig["timeout"] = parseInt(window.ank.hub[currentKey].timeout);
            }
            return componentConfig;
          });
        });
        new Vue({
          el: "#ank-hub",
          components: { "hub-main": HubMain },
          template: "<hub-main :initialData='initialData'/>",
          store: Store,
          data() {
            return {
              initialData: hubConf.data
            };
          },
          mounted() {
            enableLoader(false);
          }
        });
      });
    })
    .catch(error => {
      // Display an error message
      enableLoader(false);
      window.setTimeout(() => {
        window.alert(error);
      }, 100);
      throw error;
    });
});
