/* eslint-disable*/
import { Component, Prop, Vue, Watch } from "vue-property-decorator";
import Vuebar from "vuebar";
import { DockCollapseStatus, DockPosition } from "../HubStation/HubStationsTypes";

import {
  dockEntryEvents,
  HUB_DOCK_ENTRY_NAME
} from "./HubDockEntry/HubDockEntry.component";

Vue.use(Vuebar);

@Component({
  provide() {
    return {
      hubDock: this
    };
  }
})
export default class HubDock extends Vue {
  public get faCollapseIcon(): string {
    let collapseIcon: string = "chevron";
    let expandIcon: string = "chevron";
    switch (this.position) {
      case DockPosition.LEFT:
        collapseIcon += "-left";
        expandIcon += "-right";
        break;
      case DockPosition.RIGHT:
        collapseIcon += "-right";
        expandIcon += "-left";
        break;
      case DockPosition.TOP:
      case DockPosition.BOTTOM:
        collapseIcon += "-left";
        expandIcon += "-right";
        break;
    }
    return this.collapsed ? expandIcon : collapseIcon;
  }

  public get sizeConverted(): string {
    if (this.currentSize in Number) {
      return `${this.currentSize}px`;
    } else {
      return this.currentSize as string;
    }
  }

  public get dockStyle(): object {
    if (this.superposable) {
      switch (this.position) {
        case DockPosition.LEFT:
        case DockPosition.RIGHT:
          return {
            width: this.collapsedSize
          };
        default:
          return {};
      }
    } else {
      return this.dockWrapperStyle;
    }
  }

  public get dockWrapperStyle(): object {
    switch (this.position) {
      case DockPosition.LEFT:
      case DockPosition.RIGHT:
        return {
          width: this.sizeConverted
        };
      default:
        return {};
    }
  }

  private static getHubEntriesInstance(...slots): Vue[] {
    let result: Vue[] = [];
    if (slots && slots.length) {
      for (const nodeSlots of slots) {
        if (nodeSlots && nodeSlots.length) {
          result = result.concat(
            nodeSlots
              .filter(slot => {
                if (slot.componentInstance && slot.componentInstance.$options) {
                  return (
                    slot.componentInstance.$options.name === HUB_DOCK_ENTRY_NAME
                  );
                }
                return false;
              })
              .map(slot => slot.componentInstance)
          );
        }
      }
    }
    return result;
  }

  public $vuebar: any;

  @Prop({ default: DockPosition.LEFT }) public position!: DockPosition;
  @Prop({ default: true }) public expandable!: boolean;
  @Prop({ default: false }) public expanded!: boolean;
  @Prop({ default: "5rem", type: [String, Number] }) public collapsedSize!:
    | string
    | number;
  @Prop({ default: "15rem", type: [String, Number] }) public size!:
    | string
    | number;
  @Prop({ default: true, type: Boolean }) public collapseOnSelection!: boolean;
  @Prop({ default: false, type: Boolean }) public superposeDock!: boolean;
  @Prop({ default: true, type: Boolean }) public expandOnHover!: boolean;
  @Prop({ default: true, type: Boolean }) public superposeOnHover!: boolean;
  @Prop({ default: 1000, type: Number }) public hoverDelay!: number;
  @Prop({ default: false, type: Boolean }) public multiselection!: boolean;
  @Prop({ default: () => [], type: Array }) public content!: object[];
  @Prop({
    default: DockCollapseStatus.DefaultNonCollapsed,
    type: String
  }) public dockConfiguration!: DockCollapseStatus;

  public animate: boolean = false;
  public collapsed: boolean = !this.expanded;
  public collapsable: boolean = this.expandable;
  public superposable: boolean = this.superposeDock;
  public currentSize: string | number = this.expanded
    ? this.size
    : this.collapsedSize;
  public selectedItems: object[] = [];
  public hubEntries: Vue[] = [];

  public $refs!: {
    dockEl: HTMLElement;
    dockContent: HTMLElement;
  };
  protected overTimer: number = -1;

  @Watch("collapsed")
  public onCollapsed(val: boolean) {
    if (val) {
      this.currentSize = this.collapsedSize;
    } else {
      this.currentSize = this.size;
    }
  }

  @Watch("selectedItems")
  public onSelectedItems(val) {
    this.hubEntries.forEach((entry: any) => {
      entry.entrySelected =
        val.findIndex((i: any) => i.name === entry.name) > -1;
    });
    this.$emit("dockEntriesSelected", this.multiselection ? val : val[0]);
  }

  @Watch("dockConfiguration")
  public watchDockConfiguration(newValue) {
    switch (newValue) {
      case "NEVERCOLLAPSED":
        this.collapsable = false;
        this.collapsed = false;
        this.$emit("dockExpanded");
        break;
      case "ALWAYSCOLLAPSED":
        this.collapsed = true;
        this.collapsable = false;
        this.$emit("dockCollapsed");
        break;
      case "DEFAULTCOLLAPSED":
        this.collapsed = true;
        this.collapsable = true;
        this.$emit("dockCollapsed");
        break;
      case "DEFAULTNONCOLLAPSED":
        this.collapsed = false;
        this.collapsable = true;
        this.$emit("dockExpanded");
        break;
    }
  }

  public mounted() {
    this.hubEntries = HubDock.getHubEntriesInstance(
      this.$slots.default,
      this.$slots.header,
      this.$slots.footer
    );
    this.hubEntries.forEach(hubEntry => {
      if (hubEntry) {
        Object.keys(dockEntryEvents).forEach(key => {
          const event = dockEntryEvents[key];
          hubEntry.$on(event, this.onDockEntryEvent(event));
        });
      }
    });

    if (
      this.position === DockPosition.LEFT ||
      this.position === DockPosition.RIGHT
    ) {
      this.$vuebar.initScrollbar(this.$refs.dockContent, {});
    }
    switch (this.dockConfiguration) {
      case "NEVERCOLLAPSED":
        this.expand();
        this.collapsable = false;
        this.collapsed = false;
        this.$emit("dockExpanded");
        break;
      case "ALWAYSCOLLAPSED":
        this.collapse();
        this.collapsed = true;
        this.collapsable = false;
        this.$emit("dockCollapsed");
        break;
      case "DEFAULTCOLLAPSED":
        this.collapsed = this.storageCollapseStatus();
        this.collapsed ? this.collapse() : this.expand();
        this.collapsable = true;
        this.$emit("dockCollapsed");
        break;
      case "DEFAULTNONCOLLAPSED":
        this.collapsed = this.storageCollapseStatus();
        this.collapsed ? this.collapse() : this.expand();
        this.collapsable = true;
        this.$emit("dockExpanded");
        break;
    }
  }

  public expand() {
    this.collapsed = false;
    window.localStorage.setItem("hub-dock.collapse.status." + this.position, "expanded");
    this.$emit("dockExpanded");
    this.$emit("dockResized");
  }

  public collapse() {
    this.collapsed = true;
    window.localStorage.setItem("hub-dock.collapse.status." + this.position, "collapsed");
    this.$emit("dockCollapsed");
    this.$emit("dockResized");
  }

  public toggleDock() {
    if (
      this.position === DockPosition.BOTTOM ||
      this.position === DockPosition.TOP
    ) {
      // Use opacity animation for top/bottom docks
      this.animate = true;
      window.setTimeout(() => {
        this.animate = false;
      }, 1000);
    }

    if (this.collapsed) {
      this.expand();
    } else {
      this.collapse();
    }
  }

  protected onDockEntryEvent(eventName) {
    return eventOption => {
      switch (eventName) {
        case dockEntryEvents.selected:
          if (this.multiselection) {
            if (
              this.selectedItems.findIndex(
                (i: any) => i.name === eventOption.name
              ) === -1
            ) {
              this.selectedItems.push(eventOption);
            }
          } else {
            this.selectedItems = [eventOption];
          }
          break;
      }
    };
  }

  protected onOverDock() {
    if (this.dockConfiguration !== "ALWAYSCOLLAPSED") {
      if (this.expandOnHover && this.collapsed && this.overTimer === -1) {
        if (
          this.position === DockPosition.LEFT ||
          this.position === DockPosition.RIGHT
        ) {
          this.overTimer = window.setTimeout(() => {
            if (this.superposeOnHover && !this.superposeDock) {
              this.superposable = true;
              this.setDockWrapperAbsoluteSize();
            }
            this.expand();
          }, this.hoverDelay);
        }
      }
    }
  }

  protected onLeaveDock() {
    if (this.dockConfiguration !== "NEVERCOLLAPSED") {
      if (this.expandOnHover) {
        if (this.superposeOnHover && !this.superposeDock) {
          this.superposable = false;
          this.setDockWrapperAbsoluteSize("auto");
        }
        if (this.overTimer !== -1) {
          clearTimeout(this.overTimer);
          this.overTimer = -1;
          if (!this.collapsed) {
            this.collapse();
          }
        }
      }
    }
  }

  protected setDockWrapperAbsoluteSize(size?) {
    switch (this.position) {
      case DockPosition.LEFT:
      case DockPosition.RIGHT:
        if (size === undefined) {
          size = `${this.$refs.dockEl.offsetHeight}px`;
        }
        // @ts-ignore
        this.$refs.dockEl.style.height = size;
        break;
      default:
        break;
    }
  }

  protected storageCollapseStatus() {
    const storageCollapse = window.localStorage.getItem("hub-dock.collapse.status." + this.position);
    if (storageCollapse) {
      return storageCollapse === "collapsed";
    }
    return this.dockConfiguration === "DEFAULTNONCOLLAPSED";
  }
}
