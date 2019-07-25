import VueSetup from "../src/setup";
import publicMethods from "../mixins/AnkWebComponentsMixin/publicMethods";

const vueComponentInstall = vueComponent =>
  function componentInstall(Vue, options = { globalVueComponent: false, webComponent: false }) {
    if (componentInstall.installed === true) return;
    componentInstall.installed = true;
    Vue.use(VueSetup, options);
    if (options.webComponent) {
      Vue.customElement(vueComponent.name, vueComponent, {
        connectedCallback() {
          publicMethods(this);
        }
      });
    }
    if (options.globalVueComponent) {
      Vue.component(vueComponent.name, vueComponent);
    }
  };

export default vueComponentInstall;
