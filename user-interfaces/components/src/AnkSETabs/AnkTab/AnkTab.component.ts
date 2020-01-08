import { Component, Prop, Vue, Watch } from "vue-property-decorator";
@Component({
  name: "ank-tab"
})
export default class Tab extends Vue {
  @Prop({ default: "", type: String }) public label!: string;
  @Prop({ default: false, type: Boolean }) public disabled!: boolean;
  @Prop({ type: String, required: true }) public name!: string;
  @Prop({ default: false, type: Boolean }) public closable!: boolean;
  @Prop({ default: false, type: Boolean }) public lazy!: boolean;

  @Watch("label")
  onLabelPropCHange(newValue, oldValue) {
    this.$parent.$emit("tabLabelChanged");
  }

  public loaded: boolean = false;

  get selectedTab() {
    // @ts-ignore
    return this.$parent.selectedTab;
  }

  get isClosable() {
    // @ts-ignore
    return this.closable || this.$parent.closable;
  }

  get active() {
    // @ts-ignore
    const active = this.selectedTab === this.paneName;
    if (active) {
      this.loaded = true;
    }
    return active;
  }

  get paneName() {
    return this.name;
  }
}
