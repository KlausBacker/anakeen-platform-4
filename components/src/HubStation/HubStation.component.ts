import { AnkDock, AnkDockTab } from "@anakeen/ank-components";
import {DockPosition, HubStationDockConfigs, HubStationPropConfig, IAnkDock} from "./HubStationsTypes";
// Vue class based component export
import {Component, Prop, Vue, Watch} from "vue-property-decorator";

@Component({
  components: {
    "hub-dock": AnkDock,
    "hub-element": AnkDockTab
  }
})
export default class HubStation extends Vue {

  configData: HubStationDockConfigs = { top: [], bottom: [], left: [], right: []};

  $refs!: {
    [key: string]: IAnkDock
  };

  // region props
  @Prop({ default: () => [], type: Array}) config!: HubStationPropConfig[];
  @Prop({ default: "", type: String }) baseUrl!: string;
  // endregion props

  // region watch
  @Watch("config")
  onConfigPropChanged(val: HubStationPropConfig[]) {
    this.configData = HubStation._organizeData(val);
    console.log(this.configData);
  }
  // endregion watch

// region computed
  get isHeaderEnabled() {
      return this.configData.top.length;
  }
  get isFooterEnabled() {
    return this.configData.bottom.length;
  }

  get isLeftEnabled() {
    return this.configData.left.length;
  }

  get isRightEnabled() {
    return this.configData.right.length;
  }
  //endregion computed

  // region hooks

  //endregion hooks

  // region methods
  addHubElement(config: HubStationPropConfig) {
    const dockPosition = config.position.dock.toLowerCase();
    if (dockPosition) {
      this.configData[dockPosition].push(config);
    }
  }

  expandDock(dockPosition) {
    const ref = `dock${HubStation._capitalize(dockPosition)}`;
    if (this.$refs[ref]) {
      this.$refs[ref].expand();
    }
  }

  collapseDock(dockPosition: DockPosition) {
    const ref = `dock${HubStation._capitalize(dockPosition)}`;
    if (this.$refs[ref]) {
      this.$refs[ref].contract()
    }
  }

  _onDockTabSelected(dockPosition: DockPosition, event) {
    console.log(dockPosition, event);
  }

  private static _capitalize(str: string) {
    if (str) {
      return `${str.charAt(0).toUpperCase()}${str.slice(1)}`;
    }
    return str;
  }

  private static _organizeData(config: HubStationPropConfig[]): HubStationDockConfigs {
    return {
      top: config.filter(c => c.position.dock === DockPosition.TOP),
      bottom: config.filter(c => c.position.dock === DockPosition.BOTTOM),
      right: config.filter(c => c.position.dock === DockPosition.RIGHT),
      left: config.filter(c => c.position.dock === DockPosition.LEFT)
    };
  }
  // endregion methods
}