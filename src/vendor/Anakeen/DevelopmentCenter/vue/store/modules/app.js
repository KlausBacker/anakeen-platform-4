import {
  SET_ERROR,
  SELECT_VENDOR_CATEGORY,
  SET_CURRENT_ROUTE
} from "../mutation-types";

const mutations = {
  [SET_ERROR](state, error) {
    state.error = error;
  },
  [SELECT_VENDOR_CATEGORY](state, vendorCategory) {
    state.vendorCategory = vendorCategory;
  },
  [SET_CURRENT_ROUTE](state, route) {
    state.currentRoute = route;
  }
};

const loadInitialState = defaultState => {
  if (localStorage) {
    const saved = localStorage.getItem("devCenterAppStorage");
    if (saved) {
      return Object.assign({}, defaultState, JSON.parse(saved));
    }
  }
  return defaultState;
};

const state = loadInitialState({
  error: {},
  vendorCategory: "all",
  currentRoute: []
});

export default {
  state,
  mutations
};
