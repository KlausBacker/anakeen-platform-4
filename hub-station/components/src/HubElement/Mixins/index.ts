import { VueClass } from "vue-class-component/lib/declarations";
import { Mixins } from "vue-property-decorator";
import ElementMixin from "./HubElementMixin";

const HubElementMixin: VueClass<ElementMixin> = Mixins(ElementMixin);
export default HubElementMixin;
