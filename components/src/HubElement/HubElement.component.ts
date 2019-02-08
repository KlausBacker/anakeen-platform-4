// Vue class based component export
import {Component, Mixins, Vue} from "vue-property-decorator";
import HubElementMixins from "./Mixins/HubElementMixin";

@Component({
    mixins: [Mixins(HubElementMixins)]
})
export default class HubElement extends Vue {


}