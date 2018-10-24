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
  const visitedRoute = store.getters.visitedRoutes.find(
    r => r.path.startsWith(to.path) && r.path !== to.path
  );
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

    // Find the route definition to get children routes
    const findRouteDef = routes => {
      const found = routes.find(r => r.name === to.name);
      if (found) {
        return found;
      }
      let i = 0;
      let result = null;
      while (i < routes.length && !result) {
        const childRoute = routes[i];
        if (childRoute.children && childRoute.children.length) {
          result = findRouteDef(childRoute.children);
        }
        i++;
      }
      return result;
    };

    // Find the default child route
    const findDefaultRoute = (routeDef, fromRouteDef = null) => {
      // stop the recursion if the route contain a variable parameter
      if (fromRouteDef && routeDef.path.indexOf(":") !== -1) {
        return fromRouteDef;
      }
      if (!(routeDef.children && routeDef.children.length)) {
        return routeDef;
      }
      return findDefaultRoute(routeDef.children[0], routeDef);
    };

    const routeDef = findRouteDef(allRoutesDef);
    const defaultRouteDef = findDefaultRoute(routeDef);
    if (routeDef === defaultRouteDef) {
      next();
    } else {
      next({ name: defaultRouteDef.name, query: to.query, params: to.params });
    }
  }
});

router.afterEach(to => {
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
