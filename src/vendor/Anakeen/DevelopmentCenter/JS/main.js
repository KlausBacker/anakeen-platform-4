import Vue from "vue";
import Vuex from "vuex";
import VueRouter from "vue-router";
import { sync } from "vuex-router-sync";
import Axios from "axios";

import DevCenter from "../vue/DevCenter/DevCenter.vue";
import StoreConfig from "../vue/store";
import RouterConfig from "../vue/router";

const axios = Axios.create();
Vue.prototype.$http = axios;

Vue.use(Vuex);
Vue.use(VueRouter);

const store = new Vuex.Store(StoreConfig);
const router = new VueRouter(RouterConfig);

// Sync automatically router state in the store
sync(store, router);

new Vue({
  el: "#development-center",
  components: {
    DevCenter
  },
  store,
  router,
  template: "<dev-center></dev-center>"
});
