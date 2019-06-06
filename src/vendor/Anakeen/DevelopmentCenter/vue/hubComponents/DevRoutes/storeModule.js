import Vue from "vue";
export default {
  namespaced: true,
  state: {
    routeSection: "routes",
    routeFilter: {
      name: "",
      method: "",
      pattern: "",
      description: "",
      priority: "",
      override: ""
    },
    middlewareFilter: {
      name: "",
      method: "",
      pattern: "",
      description: "",
      priority: ""
    }
  },
  mutations: {
    SET_ROUTE_SECTION: (state, routeSection) => {
      state.routeSection = routeSection;
    },
    SET_ROUTE_FILTER: (state, routeFilter) => {
      Object.keys(routeFilter).forEach(key => {
        Vue.set(state.routeFilter, key, routeFilter[key]);
      });
    },
    SET_MIDDLEWARE_FILTER: (state, middlewareFilter) => {
      Object.keys(middlewareFilter).forEach(key => {
        Vue.set(state.routeFilter, key, middlewareFilter[key]);
      });
    }
  },
  getters: {
    routeSection: state => state.routeSection,
    routeFilter: state => state.routeFilter,
    middlewareFilter: state => state.middlewareFilter,
    routeName: state => state.routeFilter.name,
    routeMethod: state => state.routeFilter.method,
    routePattern: state => state.routeFilter.pattern,
    routeDescription: state => state.routeFilter.description,
    routePriority: state => state.routeFilter.priority,
    routeOverride: state => state.routeFilter.override,
    middlewareName: state => state.middlewareFilter.name,
    middlewareMethod: state => state.middlewareFilter.method,
    middlewarePattern: state => state.middlewareFilter.pattern,
    middlewareDescription: state => state.middlewareFilter.description,
    middlewarePriority: state => state.middlewareFilter.priority
  }
};
