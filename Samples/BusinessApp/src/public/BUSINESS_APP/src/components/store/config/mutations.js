import types from './mutations-types';
export default {
  [types.TOGGLE_COLLECTIONS](state, show) {
    state.toggleCollections = show;
  },
  [types.SELECT_COLLECTION](state, collection) {
    state.selectedCollection = collection;
  }
}