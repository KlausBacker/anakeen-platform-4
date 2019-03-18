// region mutation-types
export const SET_NOTIFICATION = "SET_NOTIFICATION";
// endregion mutation-types

export default {
  [SET_NOTIFICATION]: (state, notification) => {
    state.notification = notification;
  }
};
