export default {
  namespaced: true,
  state: {
    element: null
  },
  mutations: {
    SET_ELEMENT: (state, element) => {
      state.element = element;
    }
  },
  getters: {
    element: state => state.element
  }
};
