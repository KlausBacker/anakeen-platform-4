export default {
  namespaced: true,
  state: {
    securitySection: "smartStructures",
    ssName: "",
    ssSection: "infos",
    wflName: "",
    wflSection: "infos",
    routeAccess: "access",
    profile: "",
    fieldAccess: null,
    role: ""
  },
  mutations: {
    SET_SECURITY_SECTION: (state, securitySection) => {
      state.securitySection = securitySection;
    },
    SET_STRUCTURE_NAME: (state, ssName) => {
      state.ssName = ssName;
    },
    SET_STRUCTURE_SECTION: (state, ssSection) => {
      state.ssSection = ssSection;
    },
    SET_WFL_NAME: (state, wflName) => {
      state.wflName = wflName;
    },
    SET_WFL_SECTION: (state, wflSection) => {
      state.wflSection = wflSection;
    },
    SET_ROUTE_ACCESS: (state, routeAccess) => {
      state.routeAccess = routeAccess;
    },
    SET_PROFILE: (state, profile) => {
      state.profile = profile;
    },
    SET_FIELD_ACCESS: (state, fieldAccess) => {
      state.fieldAccess = fieldAccess;
    },
    SET_ROLE: (state, role) => {
      state.role = role;
    }
  },
  actions: {
    setSecuritySection: ({ commit }, securitySection) => {
      commit("SET_SECURITY_SECTION", securitySection);
    },
    setStructureName: ({ commit }, ssName) => {
      commit("SET_STRUCTURE_NAME", ssName);
    },
    setStructureSection: ({ commit }, ssSection) => {
      commit("SET_STRUCTURE_SECTION", ssSection);
    },
    setWflName: ({ commit }, wflName) => {
      commit("SET_WFL_NAME", wflName);
    },
    setWflSection: ({ commit }, wflSection) => {
      commit("SET_WFL_SECTION", wflSection);
    },
    setRouteAccess: ({ commit }, routeAccess) => {
      commit("SET_ROUTE_ACCESS", routeAccess);
    },
    setProfile: ({ commit }, profileId) => {
      commit("SET_PROFILE", profileId);
    }
  },
  getters: {
    securitySection: state => state.securitySection,
    ssName: state => state.ssName,
    ssSection: state => state.ssSection,
    wflName: state => state.wflName,
    wflSection: state => state.wflSection,
    routeAccess: state => state.routeAccess,
    profile: state => state.profile,
    fieldAccess: state => state.fieldAccess,
    role: state => state.role
  }
};
