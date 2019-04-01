import { Component, Inject, Prop, Vue } from "vue-property-decorator";
import { VNode } from "vue";
import { addResizeListener } from "../utils/resizeEvents";

function noop() {}
const firstUpperCase = str => {
  return str.toLowerCase().replace(/( |^)[a-z]/g, L => L.toUpperCase());
};

@Component({
  name: "ank-tabs-nav",
  components: {
    Vnodes: {
      functional: true,
      render: (h, ctx) => {
        const vnodes: VNode[] = ctx.props.vnodes;
        const classes = ctx.data.class;
        if (classes) {
          if (typeof classes === "object") {
            vnodes.forEach(
              vnode =>
                (vnode.data.class = Object.keys(classes)
                  .filter(classe => classes[classe])
                  .join(" "))
            );
          } else if (typeof classes === "string") {
            vnodes.forEach(vnode => (vnode.data.class = classes));
          }
        }
        return vnodes;
      }
    }
  }
})
export default class TabsNav extends Vue {
  // Root Vue tabs component
  @Inject("rootTabs") readonly rootTabs!: Vue;

  @Prop({ default: noop, type: Function }) readonly onTabClick!: (
    ...args: any[]
  ) => void;
  @Prop({ default: noop, type: Function }) readonly onTabRemove!: (
    ...args: any[]
  ) => void;
  @Prop({ type: Array }) readonly panes!: Vue[];

  public scrollable: boolean = false;
  public navOffset: number = 0;
  public isFocus: boolean = false;
  public focusable: boolean = true;

  public get sizeName() {
    // @ts-ignore
    return ["top", "bottom"].indexOf(this.rootTabs.tabPosition) !== -1
      ? "width"
      : "height";
  }

  public get navStyle() {
    const dir =
      // @ts-ignore
      ["top", "bottom"].indexOf(this.rootTabs.tabPosition) !== -1 ? "X" : "Y";
    return {
      transform: `translate${dir}(-${this.navOffset}px)`
    };
  }

  public labelClass(pane) {
    return {
      "ank-tab-label": true,
      // @ts-ignore
      [`is-${this.rootTabs.tabPosition}`]: true,
      "is-active": pane.active,
      "is-disabled": pane.disabled,
      // @ts-ignore
      "is-closable": pane.isClosable || this.rootTabs.editable,
      "is-focus": this.isFocus
    };
  }

  public setFocus() {
    if (this.focusable) {
      this.isFocus = true;
    }
  }

  public removeFocus() {
    this.isFocus = false;
  }

  public update() {
    if (!this.$refs.nav) return;
    const sizeName = this.sizeName;
    const navSize = this.$refs.nav[`offset${firstUpperCase(sizeName)}`];
    const containerSize = this.$refs.navScroll[
      `offset${firstUpperCase(sizeName)}`
    ];
    const currentOffset = this.navOffset;
    if (containerSize < navSize) {
      const currentOffset = this.navOffset;
      // @ts-ignore
      this.scrollable = this.scrollable || {};
      // @ts-ignore
      this.scrollable.prev = currentOffset;
      // @ts-ignore
      this.scrollable.next = currentOffset + containerSize < navSize;
      if (navSize - currentOffset < containerSize) {
        this.navOffset = navSize - containerSize;
      }
    } else {
      this.scrollable = false;
      if (currentOffset > 0) {
        this.navOffset = 0;
      }
    }
  }

  public scrollPrev() {
    const containerSize = this.$refs.navScroll[
      `offset${firstUpperCase(this.sizeName)}`
    ];
    const currentOffset = this.navOffset;
    if (!currentOffset) return;
    let newOffset =
      currentOffset > containerSize ? currentOffset - containerSize : 0;
    this.navOffset = newOffset;
  }

  public scrollNext() {
    const navSize = this.$refs.nav[`offset${firstUpperCase(this.sizeName)}`];
    const containerSize = this.$refs.navScroll[
      `offset${firstUpperCase(this.sizeName)}`
    ];
    const currentOffset = this.navOffset;
    if (navSize - currentOffset <= containerSize) return;
    let newOffset =
      navSize - currentOffset > containerSize * 2
        ? currentOffset + containerSize
        : navSize - containerSize;
    this.navOffset = newOffset;
  }

  public scrollToActiveTab() {
    if (!this.scrollable) return;
    const nav = this.$refs.nav;
    const activeTab = this.$el.querySelector(".is-active");
    if (!activeTab) return;
    const navScroll = this.$refs.navScroll;
    const activeTabBounding = activeTab.getBoundingClientRect();
    // @ts-ignore
    const navScrollBounding = navScroll.getBoundingClientRect();
    // @ts-ignore
    const maxOffset = nav.offsetWidth - navScrollBounding.width;
    const currentOffset = this.navOffset;
    let newOffset = currentOffset;
    if (activeTabBounding.left < navScrollBounding.left) {
      newOffset =
        currentOffset - (navScrollBounding.left - activeTabBounding.left);
    }
    if (activeTabBounding.right > navScrollBounding.right) {
      newOffset =
        currentOffset + activeTabBounding.right - navScrollBounding.right;
    }
    newOffset = Math.max(newOffset, 0);
    this.navOffset = Math.min(newOffset, maxOffset);
  }

  protected visibilityChangeHandler() {
    const visibility = document.visibilityState;
    if (visibility === "hidden") {
      this.focusable = false;
    } else if (visibility === "visible") {
      setTimeout(() => {
        this.focusable = true;
      }, 50);
    }
  }

  protected windowBlurHandler() {
    this.focusable = false;
  }
  protected windowFocusHandler() {
    setTimeout(() => {
      this.focusable = true;
    }, 50);
  }

  protected onClickNavItem(pane, tabName, ev) {
    this.removeFocus();
    if (this.onTabClick && typeof this.onTabClick === "function") {
      this.onTabClick(pane, tabName, ev);
    }
  }

  protected onKeydownNavItem(pane, ev) {
    if (
      // @ts-ignore
      (pane.isClosable || this.rootTabs.editable) &&
      (ev.keyCode === 46 || ev.keyCode === 8)
    ) {
      if (this.onTabRemove && typeof this.onTabRemove === "function") {
        this.onTabRemove(pane, ev);
      }
    }
  }

  protected onClickRemove(pane, ev) {
    if (this.onTabRemove && typeof this.onTabRemove === "function") {
      this.onTabRemove(pane, ev);
    }
  }

  protected onChangeTab(e) {
    const keyCode = e.keyCode;
    let nextIndex;
    let currentIndex, tabList;
    if ([37, 38, 39, 40].indexOf(keyCode) !== -1) {
      tabList = e.currentTarget.querySelectorAll("[role=tab]");
      currentIndex = Array.prototype.indexOf.call(tabList, e.target);
    } else {
      return;
    }
    if (keyCode === 37 || keyCode === 38) {
      // left
      if (currentIndex === 0) {
        // first
        nextIndex = tabList.length - 1;
      } else {
        nextIndex = currentIndex - 1;
      }
    } else {
      // right
      if (currentIndex < tabList.length - 1) {
        // not last
        nextIndex = currentIndex + 1;
      } else {
        nextIndex = 0;
      }
    }
    tabList[nextIndex].focus();
    tabList[nextIndex].click();
    this.setFocus();
  }

  public updated() {
    this.update();
  }

  public mounted() {
    addResizeListener(this.$el, this.update);
    document.addEventListener("visibilitychange", this.visibilityChangeHandler);
    window.addEventListener("blur", this.windowBlurHandler);
    window.addEventListener("focus", this.windowFocusHandler);
    setTimeout(() => {
      this.scrollToActiveTab();
    }, 0);
  }
  beforeDestroy() {
    document.removeEventListener(
      "visibilitychange",
      this.visibilityChangeHandler
    );
    window.removeEventListener("blur", this.windowBlurHandler);
    window.removeEventListener("focus", this.windowFocusHandler);
  }
}
