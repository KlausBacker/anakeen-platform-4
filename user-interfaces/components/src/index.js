// jscs:disable disallowFunctionDeclarations
import VueSetup from "./setup";
import PackageInfo from "../../package.json";

// Declare install function executed by Vue.use()
export function install(Vue) {
  if (install.installed === true) return;
  install.installed = true;
  Vue.use(VueSetup);
  const components = require("./components");
  Object.keys(components).forEach(key => {
    const component = components[key];
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
  GlobalVue = global.Vue;
}

if (GlobalVue) {
  GlobalVue.use(plugin);
}

export * from "./components";

// To allow use as module (npm/webpack/etc.) export component
export default {
  version: PackageInfo.version,
  install
};
