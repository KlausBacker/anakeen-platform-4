import RefreshButtonComponent from "./RefreshButton";

export const REFRESH_EVENT_KEY = "devCenter::refreshData";
export const RefreshDataPlugin = {
  install: function install(Vue) {
    Vue.prototype.$_busEvent = new Vue();

    Vue.mixin({
      beforeCreate() {
        if (this.$_busEvent) {
          this.$_busEvent.$on(REFRESH_EVENT_KEY, () => {
            if (
              this.$options &&
              this.$options.devCenterRefreshData &&
              typeof this.$options.devCenterRefreshData === "function"
            ) {
              this.$options.devCenterRefreshData.call(this);
              this.$emit("hook:refreshData");
            }
          });
        }
      }
    });

    Vue.component("dev-center-refresh-button", RefreshButtonComponent);
  }
};
