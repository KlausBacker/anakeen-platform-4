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

export const refreshData = (store, payload) => {
  const walkTree = (tree, walkCb = () => {}) => {
    if (tree) {
      if (typeof walkCb === "function") {
        walkCb(tree);
      }
      if (tree.$children) {
        tree.$children.forEach(child => {
          walkTree(child, walkCb);
        });
      }
    }
  };
  const vueRoot = payload.root;
  walkTree(vueRoot, vueComponent => {
    if (vueComponent && vueComponent.$options) {
      if (typeof vueComponent.$options.devCenterRefreshData === "function") {
        vueComponent.$options.devCenterRefreshData.call(vueComponent);
      }
    }
  });
};
