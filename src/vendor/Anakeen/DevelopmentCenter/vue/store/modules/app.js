import { APP_HELLO_WORLD } from "../mutation-types";

const mutations = {
  [APP_HELLO_WORLD](state) {
    state.app.sampleData = "Hello world";
  }
};

const state = {
  app: {}
};

export default {
  state,
  mutations
};
