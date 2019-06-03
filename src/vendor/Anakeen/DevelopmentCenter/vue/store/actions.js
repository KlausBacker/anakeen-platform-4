import * as mutations from "./mutation-types";

export const displayError = ({ commit }, error) => {
  commit(mutations.SET_ERROR, error);
};

export const selectVendorCategory = ({ commit }, vendorCategory) => {
  commit(mutations.SELECT_VENDOR_CATEGORY, vendorCategory);
};

export const setCurrentRoute = ({ commit }, route) => {
  commit(mutations.SET_CURRENT_ROUTE, route);
};

export const clearError = ({ commit }) => {
  commit(mutations.SET_ERROR, {});
};

export const updateRoute = () => {};