import types from './mutations-types';

export default {
  toggleCollections({ commit }, show) {
    commit(types.TOGGLE_COLLECTIONS, show);
  }
}