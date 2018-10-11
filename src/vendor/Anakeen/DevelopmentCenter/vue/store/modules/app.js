import { SET_ERROR } from "../mutation-types";

const mutations = {
  [SET_ERROR](state, error) {
    state.error = error;
  }
};

const state = {
  error: {}
};

export default {
  state,
  mutations
};
