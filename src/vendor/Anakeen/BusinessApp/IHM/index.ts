import { VueConstructor } from "vue";
import HubBusinessAppEntry from "../IHM/HubComponent/HubBusinessApp.vue";

export default function install(Vue: VueConstructor, options) {
  Vue.component("ank-business-app", HubBusinessAppEntry);
  if (options && typeof options.success === "function") {
    options.success("Business App is ready !");
  }
}
