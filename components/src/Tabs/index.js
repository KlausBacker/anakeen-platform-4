import SETabs from "./Tabs.vue";
import SETab from "./SETab/SETab.vue";
import Tab from "./Tab/Tab.vue";

export default function install(Vue) {
  Vue.component("ank-se-tabs", SETabs);
  Vue.component("ank-se-tab", SETab);
  Vue.component("ank-tab", Tab);
}
