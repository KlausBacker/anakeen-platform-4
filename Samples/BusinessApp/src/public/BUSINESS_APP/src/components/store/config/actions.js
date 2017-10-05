import types from './mutations-types';

export default {
  toggleCollections({ commit }, show) {
    commit(types.TOGGLE_COLLECTIONS, show);
  },
  selectCollection({ commit }, collection) {
    commit(types.SELECT_COLLECTION, collection);
  }
}