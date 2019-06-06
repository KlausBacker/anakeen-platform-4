export default {
  namespaced: true,
  state: {
    wflName: "",
    wflType: "infos"
  },
  mutations: {
    SET_WORKFLOW_NAME: (state, wflName) => {
      state.wflName = wflName;
    },
    SET_WORKFLOW_TYPE: (state, wflType) => {
      state.wflType = wflType;
    }
  },
  actions: {
    setWorkflowName: ({ commit }, wflName) => {
      commit("SET_WORKFLOW_NAME", wflName);
    },
    setWorkflowType: ({ commit }, wflType) => {
      commit("SET_WORKFLOW_TYPE", wflType);
    }
  },
  getters: {
    wflName: state => state.wflName,
    wflType: state => state.wflType
  }
};
