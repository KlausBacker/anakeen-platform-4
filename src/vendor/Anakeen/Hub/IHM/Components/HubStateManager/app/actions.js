import { SET_NOTIFICATION } from "./mutations";

export default {
  hubNotify({ commit }, notification) {
    commit(SET_NOTIFICATION, notification);
  },
  clearNotification({ commit }) {
    commit(SET_NOTIFICATION, null);
  }
};
