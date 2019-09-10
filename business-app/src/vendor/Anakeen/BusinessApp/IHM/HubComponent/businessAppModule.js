export default {
  mutations: {
    ADD_TAB(state, tab) {
      state.openedTabs.push(tab);
    },
    REMOVE_TAB(state, index) {
      state.openedTabs.splice(index, 1);
    },
    SET_COLLECTION(state, collection) {
      state.selectedCollection = collection;
    },
    SELECT_TAB(state, tab) {
      state.selectedTab = tab;
    }
  },
  getters: {
    tabs: state => state.openedTabs,
    selectedTab: state => state.selectedTab,
    selectedCollection: state => state.selectedCollection
  },
  namespaced: true,
  state: {
    openedTabs: [],
    selectedCollection: "",
    selectedTab: ""
  }
};
