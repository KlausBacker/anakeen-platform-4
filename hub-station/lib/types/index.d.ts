import { VueClass } from "vue-class-component/lib/declarations";
import HubElement from "./HubElement/HubElement.vue";
import ElementMixin from "./HubElement/Mixins/HubElementMixin";
import HubStation from "./HubStation/HubStation.vue";
declare const HubElementMixin: VueClass<ElementMixin>;
export { HubStation, HubElement, HubElementMixin };
