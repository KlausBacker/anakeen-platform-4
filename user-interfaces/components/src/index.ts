// eslint-disable-next-line no-unused-vars
import { VueConstructor } from "vue/types/vue";
import PackageInfo from "../../package.json";
import components from "./components-exports";
import VueSetup from "./setup";
// Ank UI Components exports

export { default as AnkAuthent } from "./AnkAuthent/AnkAuthent.vue";
export { default as AnkController } from "./AnkController";
export { default as AnkIdentity } from "./AnkIdentity/AnkIdentity.vue";
export { default as AnkLoading } from "./AnkLoading/AnkLoading.vue";
export { default as AnkLogout } from "./AnkLogout/AnkLogout.vue";
export { default as AnkSmartElement } from "./AnkSmartElement/AnkSmartElement.vue";

// Grid components
export { default as AnkSEGrid } from "./AnkSEGrid/AnkSEGrid.vue";
export { default as AnkSEGridColumnsButton } from "./AnkSEGrid/Components/GridColumnsButton/GridColumnsButton.vue";
export { default as AnkSEGridExpandButton } from "./AnkSEGrid/Components/GridExpandButton/GridExpandButton.vue";
export { default as AnkSEGridExportButton } from "./AnkSEGrid/Components/GridExportButton/GridExportButton.vue";
export { default as AnkSEGridPager } from "./AnkSEGrid/Components/GridPager/GridPager.vue";

export { default as AnkSEList } from "./AnkSEList/AnkSEList.vue";
export { default as AnkSETab } from "./AnkSETabs/AnkSETab/AnkSETab.vue";
export { default as AnkTabs } from "./AnkSETabs/AnkSETabs.vue";
export { default as AnkTab } from "./AnkSETabs/AnkTab/AnkTab.vue";
export { default as AnkSmartForm } from "./AnkSmartForm/AnkSmartForm.vue";

// Declare install function executed by Vue.use()
export function install(Vue: VueConstructor) {
  Vue.use(VueSetup);
  components.forEach(component => {
    Vue.component(component.name, component);
  });
}

// Create module definition for Vue.use()
const plugin = {
  install
};

// Auto-install when vue is found (eg. in browser via <script> tag)
let GlobalVue = null;
if (typeof window !== "undefined") {
  GlobalVue = window.Vue;
} else if (typeof global !== "undefined") {
  // @ts-ignore
  GlobalVue = global.Vue;
}

if (GlobalVue) {
  GlobalVue.use(plugin);
}

// To allow use as module (npm/webpack/etc.) export component
export default {
  install,
  version: PackageInfo.version
};
