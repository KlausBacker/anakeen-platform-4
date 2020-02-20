import { Component, Prop, Vue, Watch } from "vue-property-decorator";
import { IDomainConfig } from "./IDomainConfigType";

@Component({
  name: "ank-fullsearch-search"
})
export default class FullsearchSearchController extends Vue {
  @Prop({ default: null, type: Object })
  public domain!: IDomainConfig;
}
