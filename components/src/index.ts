import { VueClass } from "vue-class-component/lib/declarations";

const packageInfo = require("../../package.json");
import Vue from "vue";
import { Mixins } from "vue-property-decorator";
import HubElement from "./HubElement/HubElement.vue";
import ElementMixin from "./HubElement/Mixins/HubElementMixin";
import HubStation from "./HubStation/HubStation.vue";
const HubElementMixin: VueClass<ElementMixin> = Mixins(ElementMixin);
export { HubStation, HubElement, HubElementMixin };

export function install(vue: typeof Vue) {
  vue.component(`hub-station`, HubStation);
}

export default {
  install,
  version: packageInfo.version
};
