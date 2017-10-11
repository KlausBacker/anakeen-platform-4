import types from './mutations-types';
export default {
  [types.TOGGLE_COLLECTIONS](state, show) {
    state.toggleCollections = show;
  },

  [types.SELECT_COLLECTION](state, collection) {
    state.selectedCollection = collection;
  },

  [types.OPEN_DOCUMENT](state, document) {
    state.openedDocuments.push(document);
  },

  [types.SELECT_DOCUMENT](state, document) {
    state.selectedDocument = document;
  },
};
