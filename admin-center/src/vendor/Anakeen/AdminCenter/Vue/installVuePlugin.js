import VueCustomElement from "vue-custom-element";
import { PLUGIN_HOOKS } from "../Components/utils/plugins";
import VueAdminSetup from "./vueCommonConfig.js";

const callHookVueInstance = element => {
  const vueInstance = element
    ? element.__vue_custom_element__.$children[0]
    : null;
  if (vueInstance && vueInstance.$options) {
    PLUGIN_HOOKS.forEach(hookName => {
      if (typeof vueInstance.$options[hookName] === "function") {
        element[`on${hookName.toLowerCase()}`] = vueInstance.$options[
          hookName
        ].bind(vueInstance);
      }
    });
  }
};

export default (Vue, pluginName, vueDefinition, options = {}) => {
  Vue.use(VueAdminSetup);
  Vue.use(VueCustomElement);

  const DEFAULT_OPTIONS = {
    connectedCallback() {
      if (this.__vue_custom_element__) {
        callHookVueInstance(this);
      } else {
        const waitVCEReady = new MutationObserver(mutations => {
          for (let i = 0; i < mutations.length; i++) {
            const mutation = mutations[i];
            if (
              mutation.type === "attributes" &&
              mutation.attributeName === "vce-ready"
            ) {
              callHookVueInstance(mutation.target);
              waitVCEReady.disconnect();
            }
          }
        });
        waitVCEReady.observe(this, { attributes: true });
      }

      if (typeof options.connectedCallback === "function") {
        options.connectedCallback.call(this);
      }
    }
  };

  Vue.customElement(
    pluginName,
    vueDefinition,
    Object.assign({}, options, DEFAULT_OPTIONS)
  );
};
