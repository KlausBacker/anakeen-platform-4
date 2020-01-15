import { Component, Prop, Provide, Vue, Watch } from "vue-property-decorator";
import SETabsNav from "./TabNav/TabNav.vue";
import SETabsEvent from "./TabsEvent";
// eslint-disable-next-line no-unused-vars
import { TabTypes } from "./TabsTypes";

@Component({
  components: {
    "tabs-nav": SETabsNav
  },
  // @ts-ignore
  name: "ank-tabs"
})
export default class Tabs extends Vue {
  @Prop({ type: String }) public value!: string;
  @Prop({ type: String, default: null }) public selected!: string;
  @Prop({ type: String }) public type!: TabTypes;
  @Prop({ default: false, type: Boolean }) public closable!: boolean;
  @Prop({ default: false, type: Boolean }) public addable!: boolean;
  @Prop({ default: false, type: Boolean }) public sortable!: boolean;
  @Prop({ default: "12rem", type: String }) public minTabSize!: string;
  @Prop({ default: false, type: Boolean })
  public forceScrollNavigation!: boolean;
  @Prop({ default: true, type: Boolean }) public tabsList!: boolean;

  @Provide("rootTabs") public rootTabs = this;

  public panes: Vue[] = [];
  public selectedTab: string = this.value || this.selected;

  public setSelectedTab(tabName) {
    const event = new SETabsEvent(tabName);
    this.$emit("tabBeforeLeave", event);
    if (!event.isDefaultPrevented()) {
      this.selectedTab = tabName;
      this.$emit("input", tabName);
    }
  }

  public created() {
    if (!this.selectedTab) {
      this.setSelectedTab("0");
    }
  }

  public mounted() {
    this.calcPaneInstances();
  }

  public updated() {
    this.calcPaneInstances();
  }

  @Watch("selected")
  protected onSelectedPropChange(newValue) {
    this.setSelectedTab(newValue);
  }

  @Watch("value")
  protected onValuePropChange(newValue) {
    this.setSelectedTab(newValue);
  }

  @Watch("selectedTab")
  protected onSelectedTabDataChange() {
    if (this.$refs.nav) {
      this.$nextTick(() => {
        window.dispatchEvent(new Event("resize"));
        // @ts-ignore
        this.$refs.nav.$nextTick(() => {
          // @ts-ignore
          this.$refs.nav.scrollToActiveTab();
        });
      });
    }
  }

  protected calcPaneInstances() {
    if (this.$slots.default) {
      const paneSlots = this.$slots.default.filter(
        vnode =>
          vnode.tag &&
          // @ts-ignore
          vnode.componentOptions &&
          ["ank-tab", "ank-se-tab"].indexOf(
            // @ts-ignore
            vnode.componentOptions.Ctor.options.name
          ) > -1
      );
      // update indeed
      const panes = paneSlots.map(({ componentInstance }) => componentInstance);
      if (!(panes.length === this.panes.length && panes.every((pane, index) => pane === this.panes[index]))) {
        this.panes = panes;
      }
    } else if (this.panes.length !== 0) {
      this.panes = [];
    }
  }

  protected onTabClick(tab, tabName, event) {
    if (tab.disabled) {
      return;
    }
    this.setSelectedTab(tabName);
    this.$emit("tabClick", tab, event);
  }

  protected onTabRemove(pane, ev) {
    if (pane.disabled) {
      return;
    }
    ev.stopPropagation();
    this.$emit("tabEdit", pane.paneName, "remove");
    this.$emit("tabRemove", pane.paneName);
  }

  protected onTabAdd() {
    this.$emit("tabEdit", null, "add");
    this.$emit("tabAdd");
  }

  protected onTabListSelected(pane) {
    this.selectedTab = pane;
  }
}
