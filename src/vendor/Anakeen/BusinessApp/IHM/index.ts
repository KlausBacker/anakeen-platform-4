import HubBusinessAppEntry from "../IHM/HubComponent/HubBusinessApp.vue";
import { VueConstructor } from "vue";

export default function install(Vue: VueConstructor, options) {
  Vue.component("ank-business-app", HubBusinessAppEntry);
  if (options && typeof options.success === "function") {
    options.success("Business App is ready !");
  }
}
