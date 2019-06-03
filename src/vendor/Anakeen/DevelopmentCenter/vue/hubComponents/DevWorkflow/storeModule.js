export default {
  state: {
    ssName: "",
    structureTab: "SmartStructures::infos"
  },
  mutations: {
    SET_STRUCTURE_NAME: (state, ssName) => {
      state.ssName = ssName;
    },
    SET_STRUCTURE_TAB: (state, tab) => {
      state.structureTab = tab;
    }
  },
  getters: {
    ssName: state => state.ssName,
    structureTab: state => state.structureTab
  }
};
