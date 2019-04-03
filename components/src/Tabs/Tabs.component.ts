import { Component, Prop, Provide, Vue, Watch } from "vue-property-decorator";
import SETabsEvent from "./TabsEvent";
import SETabsNav from "./TabNav/TabNav.vue";
import { TabPosition, TabTypes } from "./TabsTypes";

@Component({
  // @ts-ignore
  name: "ank-tabs",
  components: {
    "tabs-nav": SETabsNav
  }
})
export default class Tabs extends Vue {
  @Prop({ type: String }) public value!: string;
  @Prop({ type: String }) public selected!: string;
  @Prop({ type: String }) public type!: TabTypes;
  @Prop({ default: false, type: Boolean }) public closable!: boolean;
  @Prop({ default: false, type: Boolean }) public addable!: boolean;
  @Prop({ default: false, type: Boolean }) public editable!: boolean;
  @Prop({ default: false, type: Boolean }) public sortable!: boolean;
  @Prop({ default: false, type: Boolean })
  public forceScrollNavigation!: boolean;
  @Prop({ default: TabPosition.TOP, type: String })
  public tabPosition!: TabPosition;
  @Prop({ default: true, type: Boolean }) public tabsList!: boolean;

  @Watch("selected")
  onSelectedPropChange(newValue) {
    this.setSelectedTab(newValue);
  }

  @Watch("value")
  onValuePropChange(newValue) {
    this.setSelectedTab(newValue);
  }

  @Watch("selectedTab")
  onSelectedTabDataChange(newValue, oldValue) {
    if (this.$refs.nav) {
      this.$nextTick(() => {
        // @ts-ignore
        this.$refs.nav.$nextTick(_ => {
          // @ts-ignore
          this.$refs.nav.scrollToActiveTab();
        });
      });
    }
  }

  @Provide("rootTabs") rootTabs = this;

  public panes: Vue[] = [];
  public selectedTab: string = this.value || this.selected;

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
      if (
        !(
          panes.length === this.panes.length &&
          panes.every((pane, index) => pane === this.panes[index])
        )
      ) {
        this.panes = panes;
      }
    } else if (this.panes.length !== 0) {
      this.panes = [];
    }
  }

  protected onTabClick(tab, tabName, event) {
    if (tab.disabled) return;
    this.setSelectedTab(tabName);
    this.$emit("tabClick", tab, event);
  }

  protected onTabRemove(pane, ev) {
    if (pane.disabled) return;
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

  public setSelectedTab(tabName) {
    const event = new SETabsEvent(tabName);
    this.$emit("tabBeforeLeave", event);
    if (!event.isDefaultPrevented()) {
      this.selectedTab = tabName;
      this.$emit("input", tabName);
    }
  }

  created() {
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
}
