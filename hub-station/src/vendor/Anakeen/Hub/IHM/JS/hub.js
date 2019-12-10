import Vue from "vue";
import $ from "jquery";
import kendo from "@progress/kendo-ui/js/kendo.progressbar";

import setup from "@anakeen/user-interfaces/components/lib/setup.esm";
import HubMain from "../Components/Hub/Hub";
import store from "../Components/HubStateManager";
import HubEntry from "@anakeen/hub-components/lib/AnkHubUtil.esm";

//Share vue between all hub elements
window.vue = Vue;
window.hub = window.hub || {};
window.hub.store = window.hub.store || store;

Vue.use(setup);

const hubConf = new HubEntry(window.AnkHubInstanceId);

const enableLoader = (enable = true, element = "body") => {
  kendo.ui.progress($(element), enable);
};

enableLoader();

Vue.$_globalI18n.recordCatalog().then(() => {
  hubConf
    .initializeHub()
    .then(hubData => {
      new Vue({
        el: "#ank-hub",
        components: { "hub-main": HubMain },
        template: "<hub-main :initialData='initialData'/>",
        store: store,
        data() {
          return {
            initialData: hubData
          };
        },
        mounted() {
          enableLoader(false);
        }
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
