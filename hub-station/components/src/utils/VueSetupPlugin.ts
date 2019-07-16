import VueEventBus from "./VueEventBus";
import HubElementLayout from "../HubElement/HubElementLayout/HubElementLayout.vue";

export default function install(Vue) {
  if (!Vue.prototype.$_hubEventBus) {
    Vue.prototype.$_hubEventBus = new VueEventBus();
  }
  Vue.component("hub-element-layout", HubElementLayout);
}
