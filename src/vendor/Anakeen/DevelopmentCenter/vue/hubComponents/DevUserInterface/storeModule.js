export default {
  namespaced: true,
  state: {
    ssName: "",
    uiSection: "infos"
  },
  mutations: {
    SET_STRUCTURE_NAME: (state, ssName) => {
      state.ssName = ssName;
    },
    SET_UI_SECTION: (state, uiSection) => {
      state.uiSection = uiSection;
    }
  },
  actions: {
    setStructureName: ({ commit }, ssName) => {
      commit("SET_STRUCTURE_NAME", ssName);
    },
    setUiSection: ({ commit }, uiSection) => {
      commit("SET_UI_SECTION", uiSection);
    }
  },
  getters: {
    ssName: state => state.ssName,
    uiSection: state => state.uiSection
  }
};
