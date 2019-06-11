export default {
  namespaced: true,
  state: {
    context: "",
    msgid: "",
    en: "",
    fr: "",
    files: ""
  },
  mutations: {
    SET_CONTEXT: (state, context) => {
      state.context = context;
    },
    SET_MSGID: (state, msgid) => {
      state.msgid = msgid;
    },
    SET_EN: (state, en) => {
      state.en = en;
    },
    SET_FR: (state, fr) => {
      state.fr = fr;
    },
    SET_FILES: (state, files) => {
      state.files = files;
    }
  },
  getters: {
    context: state => state.context,
    msgid: state => state.msgid,
    en: state => state.en,
    fr: state => state.fr,
    files: state => state.files
  }
};
