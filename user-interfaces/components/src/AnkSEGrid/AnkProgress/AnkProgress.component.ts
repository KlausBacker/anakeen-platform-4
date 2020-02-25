import { Component, Prop, Watch, Vue } from "vue-property-decorator";

@Component({
  name: "ank-progress"
})
export default class Progress extends Vue {
  @Prop({
    default: false,
    type: Boolean
  })
  public loading: boolean;

  @Prop({
    default: "",
    type: String
  })
  public anchorClass: string;

  public $refs!: {
    overlay: HTMLElement;
  };

  @Watch("anchorClass")
  protected onAnchorClassChange(anchorClass) {
    this.setOverlaySize(anchorClass);
  }

  public mounted() {
    this.setOverlaySize(this.anchorClass);
  }

  protected setOverlaySize(anchorClass: string = "") {
    let size = null;
    if (this.$slots.default && this.$slots.default.length) {
      const element = this.$slots.default[0].elm as HTMLElement;
      if (!anchorClass) {
        size = element.getBoundingClientRect();
      } else {
        let find = element.getElementsByClassName(anchorClass);
        if (find && find.length) {
          size = find[0].getBoundingClientRect();
        }
      }
      if (size && this.$refs.overlay) {
        this.$refs.overlay.style.top = size.top + "px";
        this.$refs.overlay.style.left = size.left + "px";
        this.$refs.overlay.style.width = size.width + "px";
        this.$refs.overlay.style.height = size.height + "px";
      }
    }
  }
}
