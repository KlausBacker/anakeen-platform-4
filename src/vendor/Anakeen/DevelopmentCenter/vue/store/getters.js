export const currentStoredRoute = state => {
  return state.route;
};

export const vendorCategory = state => {
  return state.app.vendorCategory;
};

export const visitedRoutes = state => {
  return state.app.visitedRoutes;
};

export const vendorTypeUrl = (state, getters) => {
  let value = getters.vendorCategory;
  if (value === "anakeen") {
    return "all";
  }
  return value;
};
