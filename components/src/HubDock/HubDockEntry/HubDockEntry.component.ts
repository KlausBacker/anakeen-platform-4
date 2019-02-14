/* eslint-disable*/
import { Component, Inject, Prop, Vue, Watch } from "vue-property-decorator";

export const HUB_DOCK_ENTRY_NAME = "HubDockEntry";
export const dockEntryEvents = {
  selected: "dockEntrySelected"
};

@Component({
  name: HUB_DOCK_ENTRY_NAME
})
export default class HubDockEntry extends Vue {
  @Prop({ default: false }) public evenSpace!: boolean;
  @Prop({ required: true }) public name!: string;
  @Prop({ default: false }) public selected!: boolean;
  @Prop({ type: [String, Object] }) public route!: string | object;

  public entrySelected: boolean = false;

  @Inject() public hubDock!: any;

  @Watch("selected")
  public onSelectedPropChange(val) {
    this.entrySelected = val;
  }

  get isCollapsed(): boolean {
    return this.hubDock.collapsed;
  }

  get entryStyle(): object {
    if (this.hubDock.evenSpace || this.evenSpace) {
      return {
        height: "100%",
        width: "100%"
      };
    }
    return {};
  }

  get entryConfiguration(): object {
    return {
      name: this.name
    };
  }

  public selectEntry() {
    this.entrySelected = true;
    this.$emit(dockEntryEvents.selected, this.entryConfiguration);
    if (this.route && this.$router) {
      this.$router.push(this.route);
    }
  }
}
