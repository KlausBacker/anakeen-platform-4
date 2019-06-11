export default {
  namespaced: true,
  state: {
    ssName: "",
    ssType: "infos",
    ssDetails: ""
  },
  mutations: {
    SET_STRUCTURE_NAME: (state, ssName) => {
      state.ssName = ssName;
    },
    SET_STRUCTURE_TYPE: (state, ssType) => {
      state.ssType = ssType;
    },
    SET_STRUCTURE_DETAILS: (state, ssDetails) => {
      state.ssDetails = ssDetails;
    }
  },
  actions: {
    setStructureName: ({ commit }, ssName) => {
      commit("SET_STRUCTURE_NAME", ssName);
    },
    setStructureType: ({ commit }, ssType) => {
      commit("SET_STRUCTURE_TYPE", ssType);
    },
    setStructureDetails: ({ commit }, ssDetails) => {
      commit("SET_STRUCTURE_DETAILS", ssDetails);
    }
  },
  getters: {
    ssName: state => state.ssName,
    ssType: state => state.ssType,
    ssDetails: state => state.ssDetails
  }
};
