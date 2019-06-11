export default {
  namespaced: true,
  state: {
    ssName: "",
    uiSection: "infos",
    mask: "",
    control: null
  },
  mutations: {
    SET_STRUCTURE_NAME: (state, ssName) => {
      state.ssName = ssName;
    },
    SET_UI_SECTION: (state, uiSection) => {
      state.uiSection = uiSection;
    },
    SET_MASK: (state, mask) => {
      state.mask = mask;
    },
    SET_CONTROL: (state, control) => {
      state.control = control;
    }
  },
  actions: {
    setStructureName: ({ commit }, ssName) => {
      commit("SET_STRUCTURE_NAME", ssName);
    },
    setUiSection: ({ commit }, uiSection) => {
      commit("SET_UI_SECTION", uiSection);
    },
    setMask: ({ commit }, mask) => {
      commit("SET_MASK", mask);
    }
  },
  getters: {
    ssName: state => state.ssName,
    uiSection: state => state.uiSection,
    mask: state => state.mask,
    control: state => state.control
  }
};
