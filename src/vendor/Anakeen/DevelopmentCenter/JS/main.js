import Vue from "vue";
import Vuex from "vuex";
import VueRouter from "vue-router";
import VueRouterMultiView from "vue-router-multi-view";

import { sync } from "vuex-router-sync";
import Axios from "axios";

import DevCenter from "../vue/DevCenter/DevCenter.vue";
import StoreConfig from "../vue/store";
import RouterConfig from "../vue/router";
import RouterTabs from "../vue/components/RouterTabs/RouterTabs.vue";

const axios = Axios.create();
Vue.prototype.$http = axios;

Vue.use(VueRouterMultiView);
Vue.use(Vuex);
Vue.use(VueRouter);

VueRouter.prototype.addQueryParams = function(queryParams) {
  const currentQuery = this.currentRoute ? this.currentRoute.query || {} : {};
  this.push({ query: Object.assign({}, currentQuery, queryParams) });
};

const store = new Vuex.Store(StoreConfig);
const router = new VueRouter(RouterConfig);

// Sync automatically router state in the store
sync(store, router);

Vue.component(RouterTabs.name, RouterTabs);
new Vue({
  el: "#development-center",
  components: {
    DevCenter
  },
  store,
  router,
  template: "<dev-center></dev-center>"
});
