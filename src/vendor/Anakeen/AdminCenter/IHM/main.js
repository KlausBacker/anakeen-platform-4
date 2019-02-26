import Vue from "vue";

// import router from "./router";
import { HubStation } from "@anakeen/hub-components";
import axios from "axios";
import ParametersEntry from "../HubComponent/AdminCenterParameters/AdminCenterParametersEntry.vue";
import AuthenticationTokensEntry from "../HubComponent/AuthenticationTokensHub/AuthenticationTokensHubComponent.vue";
import VaultManager from "../HubComponent/AdminCenterVaultManager/AdminCenterVaultManagerEntry.vue";
import VueRouter from "vue-router";

Vue.use(VueRouter);
const router = new VueRouter({
  mode: "history"
});
Vue.component("ank-admin-parameter", ParametersEntry);
Vue.component("ank-hub-authentication-tokens", AuthenticationTokensEntry);
Vue.component("ank-admin-vault-manager", VaultManager);
new Vue({
  el: "#admin-center",
  template: '<hub-station :config="config" baseUrl="/admin"></hub-station>',
  components: {
    HubStation
  },
  data() {
    return {
      config: []
    };
  },
  created: function() {
    axios.get("/hub/config/ADMINCENTER").then(response => {
      this.config = response.data.data;
    });
  },
  router
});
