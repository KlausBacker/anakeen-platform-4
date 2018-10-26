import Vue from "vue";
import Vuex from "vuex";
import VueRouter from "vue-router";
import VueRouterMultiView from "vue-router-multi-view";

import { sync } from "vuex-router-sync";
import Axios from "axios";

import DevCenter from "../vue/DevCenter/DevCenter.vue";
import StoreConfig from "../vue/store";
import RouterConfig from "../vue/router";
import * as RouterUtils from "../vue/router/utils";
import RouterTabs from "../vue/components/RouterTabs/RouterTabs.vue";
import SSList from "../vue/components/SSList/SSList.vue";

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

router.beforeEach((to, from, next) => {
  const visitedRoute = store.getters.visitedRoutes.find(visited => {
    return visited.path !== to.path && RouterUtils.startsWithRoute(visited, to);
  });
  if (visitedRoute) {
    // Redirect to the already visited route that match the destination
    next({
      name: visitedRoute.name,
      params: visitedRoute.params,
      query: visitedRoute.query
    });
  } else {
    // If the route is never visited, try to redirect to default child route
    const allRoutesDef = router.options.routes;

    const routeDef = RouterUtils.findRouteDef(to)(allRoutesDef);
    const defaultRouteDef = RouterUtils.findDefaultRoute(routeDef);
    if (routeDef === defaultRouteDef) {
      next();
    } else {
      next({ name: defaultRouteDef.name, query: to.query, params: to.params });
    }
  }
});

router.afterEach(to => {
  // Save the visited route
  store.dispatch("updateVisitedRoute", to);
});

// Sync automatically router state in the store
sync(store, router);

Vue.component(RouterTabs.name, RouterTabs);
Vue.component(SSList.name, SSList);

new Vue({
  el: "#development-center",
  components: {
    DevCenter
  },
  store,
  router,
  template: "<dev-center></dev-center>"
});
