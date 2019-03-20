// jscs:disable disallowFunctionDeclarations
import VueSetup from "./setup";
import { publicMethods } from "../mixins/AnkWebComponentsMixin";
import PackageInfo from "../../package.json";

// Declare install function executed by Vue.use()
export function install(
  Vue,
  opts = { globalVueComponents: false, webComponents: false }
) {
  if (install.installed === true) return;
  install.installed = true;
  Vue.use(VueSetup, opts);
  const components = require("./components");
  if (opts.webComponents) {
    // register Vue src to web src
    Object.keys(components).forEach(key => {
      const component = components[key];
      Vue.customElement(component.name, component, {
        connectedCallback() {
          publicMethods(this);
        }
      });
    });
  }
  if (opts.globalVueComponents) {
    Object.keys(components).forEach(key => {
      const component = components[key];
      Vue.component(component.name, component);
    });
  }
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
