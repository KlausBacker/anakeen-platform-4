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
  }
};

const state = {
  error: {},
  vendorCategory: "anakeen",
};

export default {
  state,
  mutations
};
