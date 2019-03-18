// Vue class based component export
import { Component, Mixins, Vue } from "vue-property-decorator";
import HubElementMixins from "./Mixins/HubElementMixin";

@Component({
  inject: ["$_hubStation"],
  mixins: [Mixins(HubElementMixins)]
})
export default class HubElement extends Vue {
  public hubNotify(notification = {}) {
    // @ts-ignore
    this.$_hubStation.$emit("hubNotify", notification);
  }
}
