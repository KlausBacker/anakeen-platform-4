export default {
  namespaced: true,
  state: {
    routeSection: "routes"
  },
  mutations: {
    SET_ROUTE_SECTION: (state, routeSection) => {
      state.routeSection = routeSection;
    }
  },
  getters: {
    routeSection: state => state.routeSection
  }
};
