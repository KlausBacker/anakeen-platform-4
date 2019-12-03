import HubElementLayout from "../HubElement/HubElementLayout/HubElementLayout.vue";
import VueEventBus from "./VueEventBus";

export default function install(Vue) {
  if (!Vue.prototype.$_hubEventBus) {
    Vue.prototype.$_hubEventBus = new VueEventBus();
  }
  Vue.component("hub-element-layout", HubElementLayout);
}
