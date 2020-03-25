export default function() {
  return {
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
      },
      UPDATE_TAB(state, { previousId, newTab }) {
        state.openedTabs = state.openedTabs.map(t => {
          if (t.tabId === undefined && t.name === previousId) {
            return newTab;
          } else if (t.tabId === previousId) {
            return newTab;
          } else {
            return t;
          }
        });
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
}
