/* eslint-disable*/
import {Component, Vue, Inject, Prop, Watch } from "vue-property-decorator";

export const HUB_DOCK_ENTRY_NAME = "HubDockEntry";
export const dockEntryEvents = {
  selected: "dockEntrySelected"
};

@Component({
  name: HUB_DOCK_ENTRY_NAME
})
export default class HubDockEntry extends Vue {

  @Prop({ default: false}) evenSpace!: boolean;
  @Prop({ required: true}) name!: string;
  @Prop({ default: false }) selected!: boolean;

  entrySelected: boolean = false;

  @Inject() hubDock!: any;

  @Watch("selected")
  onSelectedPropChange(val) {
    this.entrySelected = val;
  }

  get isCollapsed(): boolean {
    return this.hubDock.collapsed;
  }

  get entryStyle(): object {
    if (this.hubDock.evenSpace || this.evenSpace) {
      return {
        width: "100%",
        height: "100%"
      };
    }
    return {};
  }

  get entryConfiguration(): object {
    return {
      name: this.name
    }
  }

  selectEntry() {
    this.entrySelected = true;
    this.$emit(dockEntryEvents.selected, this.entryConfiguration)
  }
}
