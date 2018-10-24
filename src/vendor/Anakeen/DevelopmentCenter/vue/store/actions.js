import * as mutations from "./mutation-types";

export const displayError = ({ commit }, error) => {
  commit(mutations.SET_ERROR, error);
};

export const selectVendorCategory = ({ commit }, vendorCategory) => {
  commit(mutations.SELECT_VENDOR_CATEGORY, vendorCategory);
};

export const updateVisitedRoute = ({ commit }, route) => {
  commit(mutations.UPDATE_VISITED_ROUTE, route);
};

export const clearError = ({ commit }) => {
  commit(mutations.SET_ERROR, {});
};
