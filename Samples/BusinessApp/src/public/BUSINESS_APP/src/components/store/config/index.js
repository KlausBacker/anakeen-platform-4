import Vuex from 'vuex';
import Vue from 'vue';

import actions from './actions';
import getters from './getters';
import state from './emptyState';
import mutations from './mutations';

Vue.use(Vuex);

export default new Vuex.Store({
  state,
  mutations,
  actions,
  getters
});