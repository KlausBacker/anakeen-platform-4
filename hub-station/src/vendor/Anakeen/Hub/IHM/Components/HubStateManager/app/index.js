import mutations from "./mutations";
import actions from "./actions";
import getters from "./getters";

const APP_INITIAL_STATE = {
  notification: null
};

export default {
  state: APP_INITIAL_STATE,
  mutations,
  actions,
  getters
};
