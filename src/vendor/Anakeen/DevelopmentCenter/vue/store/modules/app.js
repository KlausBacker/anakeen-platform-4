import {
  SET_ERROR,
  SELECT_VENDOR_CATEGORY,
  UPDATE_VISITED_ROUTE
} from "../mutation-types";

const mutations = {
  [SET_ERROR](state, error) {
    state.error = error;
  },
  [SELECT_VENDOR_CATEGORY](state, vendorCategory) {
    state.vendorCategory = vendorCategory;
  },
  [UPDATE_VISITED_ROUTE](state, route) {
    const routeIndex = state.visitedRoutes.findIndex(
      r => r.path === route.path
    );
    if (routeIndex !== -1) {
      state.visitedRoutes.splice(routeIndex, 1);
    }
    state.visitedRoutes.unshift(route);
  }
};

const state = {
  error: {},
  vendorCategory: "anakeen",
  visitedRoutes: []
};

export default {
  state,
  mutations
};
