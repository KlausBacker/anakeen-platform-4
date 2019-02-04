import HubDock from "../HubDock/HubDock.vue";
import HubDockEntry from "../HubDock/HubDockEntry/HubDockEntry.vue";
import {DockPosition, HubStationDockConfigs, HubStationPropConfig, IAnkDock} from "./HubStationsTypes";
// Vue class based component export
import {Component, Prop, Vue, Watch} from "vue-property-decorator";

@Component({
  components: {
    "hub-dock": HubDock,
    "hub-dock-entry": HubDockEntry
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

  get DockPosition(): any {
    return DockPosition;
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

  getDockHeaders(configs: HubStationPropConfig[]) {
    return configs.filter(c => {
      if (c.position.dock === DockPosition.TOP || c.position.dock === DockPosition.BOTTOM) {
        return c.position.innerPosition === DockPosition.LEFT;
      } else {
        return c.position.innerPosition === DockPosition.TOP;
      }
    })
  }

  getDockContent(configs: HubStationPropConfig[]) {
    return configs.filter(c => {
      return c.position.innerPosition === DockPosition.CENTER;
    })
  }

  getDockFooter(configs: HubStationPropConfig[]) {
    return configs.filter(c => {
      if (c.position.dock === DockPosition.TOP || c.position.dock === DockPosition.BOTTOM) {
        return c.position.innerPosition === DockPosition.RIGHT;
      } else {
        return c.position.innerPosition === DockPosition.BOTTOM;
      }
    })
  }

  onDockEntrySelected(entry) {
    const component = Vue.component(entry.component.name);
    // Create component instance
    const instance = new component({
      propsData: entry.component.props
    });
    // Get dom content ref
    // @ts-ignore
    const domRef = instance.$options.getHubConfiguration().contentEl;
    if (domRef) {
      instance.$mount(domRef);
    }
  }

  getCollapsedTemplate(config: HubStationPropConfig) {
    // Get component constructor
    const component: any = Vue.component(config.component.name);
    // Get Hub component configuration
    if (component && component.options && component.options.getHubConfiguration) {
      const template = component.options.getHubConfiguration().collapsedTemplate;
      return Vue.extend({
        template
      });
    }
    return ""
  }

  getExpandedTemplate(config) {
    // Get component constructor
    const component: any = Vue.component(config.component.name);
    // Get Hub component configuration
    if (component && component.options && component.options.getHubConfiguration) {
      const template = component.options.getHubConfiguration().expandedTemplate;
      return Vue.extend({
        template
      });
    }
    return ""
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