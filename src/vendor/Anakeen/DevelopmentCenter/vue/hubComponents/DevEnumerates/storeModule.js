export default {
  namespaced: true,
  state: {
    name: "",
    key: "",
    label: "",
    parentkey: "",
    disabled: ""
  },
  mutations: {
    SET_NAME: (state, name) => {
      state.name = name;
    },
    SET_KEY: (state, key) => {
      state.key = key;
    },
    SET_LABEL: (state, label) => {
      state.label = label;
    },
    SET_PARENTKEY: (state, parentkey) => {
      state.parentkey = parentkey;
    },
    SET_DISABLED: (state, disabled) => {
      disabled;
      state.disabled = disabled;
    }
  },
  getters: {
    name: state => state.name,
    label: state => state.label,
    key: state => state.key,
    parentkey: state => state.parentkey,
    disabled: state => state.disabled
  }
};
