const packageInfo = require("../package.json");
import { VueConstructor } from "vue/types/vue";
import VueAxiosPlugin from "./AxiosPlugin/AxiosPlugin";
import AnkSplitter from "./Splitter/Splitter.vue";

export { AnkSplitter, VueAxiosPlugin };

export function install(Vue: VueConstructor) {
  Vue.component(AnkSplitter.name, AnkSplitter);
}

export default {
  install,
  name: packageInfo.name,
  version: packageInfo.version
};

const plugin = {
  install,
  name: packageInfo.name,
  version: packageInfo.version
};

interface IWindow extends Window {
  Vue: VueConstructor;
}
interface IGlobal extends NodeJS.Global {
  Vue: VueConstructor;
}
declare const window: IWindow;
declare const global: IGlobal;

// Auto install
let GlobalVue: VueConstructor | null = null;
if (window.Vue) {
  GlobalVue = window.Vue;
} else if (global.Vue) {
  GlobalVue = global.Vue;
}

if (GlobalVue) {
  GlobalVue.use(plugin);
}
