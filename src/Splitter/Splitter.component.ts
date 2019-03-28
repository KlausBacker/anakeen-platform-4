import { LayoutInstaller } from "@progress/kendo-layout-vue-wrapper";
import "@progress/kendo-ui/js/kendo.splitter";
import { Component, Prop, Vue } from "vue-property-decorator";
Vue.use(LayoutInstaller);

@Component({
  name: "ank-splitter"
})
export default class SplitterComponent extends Vue {
  public $refs: any;

  @Prop({ type: String, default: "" })
  public localStorageKey!: string;

  @Prop({ type: Array, default: [] })
  public panes!: object[];

  public splitterEmpty: boolean = true;
  public mounted() {
    if (this.$refs.ankSplitter) {
      this.$refs.ankSplitter.kendoWidget().setOptions(this.$attrs);
    }
    if (this.localStorageKey && window.localStorage) {
      const savedSize = window.localStorage.getItem(this.localStorageKey);
      if (savedSize) {
        this.$refs.ankSplitter.kendoWidget().size(".k-pane:first", savedSize);
      }
      this.$refs.ankSplitter
        .kendoWidget()
        .bind("resize", this.onSplitterResize);
    }
  }

  public onSplitterResize() {
    window.localStorage.setItem(
      this.localStorageKey,
      this.$refs.ankSplitter.kendoWidget().size(".k-pane:first")
    );
  }
  public disableEmptyContent() {
    this.splitterEmpty = false;
  }

  public enableEmptyContent() {
    this.splitterEmpty = true;
  }

  public toggleEmptyContent() {
    this.splitterEmpty = !this.splitterEmpty;
  }

  public isEmptyContent() {
    return this.splitterEmpty;
  }

  public expandPane(pane) {
    let realPane = pane;
    if (pane === "right") {
      realPane = ".k-pane:last";
    } else if (pane === "left") {
      realPane = ".k-pane:first";
    }
    this.$refs.ankSplitter.expand(realPane);
  }

  public collapsePane(pane) {
    let realPane = pane;
    if (pane === "right") {
      realPane = ".k-pane:last";
    } else if (pane === "left") {
      realPane = ".k-pane:first";
    }
    this.$refs.ankSplitter.collapse(realPane);
  }
}
