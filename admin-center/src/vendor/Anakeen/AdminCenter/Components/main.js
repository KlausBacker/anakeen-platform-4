import Vue from "vue";
import LoadScript from "vue-plugin-load-script";
import AnkComponents from "ank-components";
import "@progress/kendo-ui/js/kendo.dialog";
import "@progress/kendo-ui/js/kendo.notification";
import { DialogInstaller } from "@progress/kendo-dialog-vue-wrapper";
import store from "./store";
import router from "./router";
import AdminCenter from "./AdminCenter.vue";
import AnkAxios from "./utils/axios";
import { onAuthError, onNetworkError } from "./utils/xhrErrors";
import { PLUGIN_SCHEMA } from "./utils/plugins";

Vue.use(AnkComponents, { webComponents: true });
Vue.use(DialogInstaller);
Vue.use(LoadScript);
Vue.use(AnkAxios, {
  onErrorResponse: error => {
    if (error.response === undefined) {
      onNetworkError(store);
    } else if (error.response.status === 403) {
      onAuthError(store);
    }
    return error;
  }
});

router.afterEach(to => {
  const toPluginPath = to.path;
  let rootPath = toPluginPath;
  const matched = toPluginPath.match(/(\/[a-zA-Z0-9-_]+)\/?/);
  if (matched && matched.length > 1) {
    rootPath = matched[1];
  }
  const rootPlugin = store.getters.getPluginsList.find(
    p => p[PLUGIN_SCHEMA.pluginPath] === rootPath
  );
  if (rootPlugin) {
    store.commit("SET_ROOT_PLUGIN", rootPlugin);
  }
});

Vue.jQuery = Vue.jquery = Vue.prototype.$ = kendo.jQuery;
Vue.kendo = Vue.prototype.$kendo = kendo;

new Vue({
  el: "#admin-center",
  template: "<admin-center/>",
  components: {
    AdminCenter
  },
  store,
  router
});
