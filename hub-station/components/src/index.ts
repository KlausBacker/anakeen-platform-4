import { VueClass } from "vue-class-component/lib/declarations";

import { Mixins } from "vue-property-decorator";
import HubElement from "./HubElement/HubElement.vue";
import ElementMixin from "./HubElement/Mixins/HubElementMixin";
import HubStation from "./HubStation/HubStation.vue";
const HubElementMixin: VueClass<ElementMixin> = Mixins(ElementMixin);
export { HubStation, HubElement, HubElementMixin };

export default HubElement;
