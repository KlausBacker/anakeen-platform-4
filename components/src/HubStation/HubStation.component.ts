const nodePath = require("path");
import AnkComponents from "@anakeen/ank-components";
// Vue class based component export
import { Component, Prop, Vue, Watch } from "vue-property-decorator";
import HubDock from "../HubDock/HubDock.vue";
import HubDockEntry from "../HubDock/HubDockEntry/HubDockEntry.vue";
import { HubElementDisplayTypes } from "../HubElement/HubElementTypes";
import {
  DockPosition,
  IAnkDock,
  IHubStationDockConfigs,
  IHubStationPropConfig,
  InnerDockPosition
} from "./HubStationsTypes";
Vue.use(AnkComponents, { globalVueComponents: true });
@Component({
  components: {
    "hub-dock": HubDock,
    "hub-dock-entry": HubDockEntry
  }
})
export default class HubStation extends Vue {
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

  get HubElementDisplayTypes(): any {
    return HubElementDisplayTypes;
  }

  private static capitalize(str: string) {
    if (str) {
      return `${str.charAt(0).toUpperCase()}${str.slice(1)}`;
    }
    return str;
  }

  private static organizeData(
    config: IHubStationPropConfig[]
  ): IHubStationDockConfigs {
    return {
      bottom: config.filter(c => c.position.dock === DockPosition.BOTTOM),
      left: config.filter(c => c.position.dock === DockPosition.LEFT),
      right: config.filter(c => c.position.dock === DockPosition.RIGHT),
      top: config.filter(c => c.position.dock === DockPosition.TOP)
    };
  }

  public configData: IHubStationDockConfigs = {
    bottom: [],
    left: [],
    right: [],
    top: []
  };

  public $refs!: {
    [key: string]: IAnkDock;
  };

  // region props
  @Prop({ default: () => [], type: Array })
  public config!: IHubStationPropConfig[];
  @Prop({ default: "", type: String }) public baseUrl!: string;
  // endregion props

  // region watch
  @Watch("config")
  public onConfigPropChanged(val: IHubStationPropConfig[]) {
    this.configData = HubStation.organizeData(val);
    this.initRouterConfig(this.configData);
  }
  // endregion computed

  // region hooks

  // endregion hooks

  // region methods
  public addHubElement(config: IHubStationPropConfig) {
    const dockPosition = config.position.dock.toLowerCase();
    if (dockPosition) {
      this.configData[dockPosition].push(config);
    }
  }

  public expandDock(dockPosition) {
    const ref = `dock${HubStation.capitalize(dockPosition)}`;
    if (this.$refs[ref]) {
      this.$refs[ref].expand();
    }
  }

  public collapseDock(dockPosition: DockPosition) {
    const ref = `dock${HubStation.capitalize(dockPosition)}`;
    if (this.$refs[ref]) {
      this.$refs[ref].contract();
    }
  }

  public getDockHeaders(configs: IHubStationPropConfig[]) {
    return configs.filter(c => {
      return c.position.innerPosition === InnerDockPosition.HEADER;
    });
  }

  public getDockContent(configs: IHubStationPropConfig[]) {
    return configs.filter(c => {
      return c.position.innerPosition === InnerDockPosition.CENTER;
    });
  }

  public getDockFooter(configs: IHubStationPropConfig[]) {
    return configs.filter(c => {
      return c.position.innerPosition === InnerDockPosition.FOOTER;
    });
  }

  public initRouterConfig(configData: IHubStationDockConfigs) {
    Object.keys(configData).forEach(key => {
      const routes = this.getRoutesConfigs(configData[key]);
      if (this.$router) {
        this.$nextTick(() => {
          this.$router.addRoutes(routes);
        });
      }
    });
  }

  public getEntryRoutePath(entryOptions) {
    if (entryOptions && entryOptions.route) {
      return nodePath.join(this.baseUrl, entryOptions.route);
    }
    return "";
  }

  public mounted() {
    this.initRouterConfig(this.configData);
  }

  private getRoutesConfigs(configs: IHubStationPropConfig[]) {
    const routes: any[] = [];
    if (configs && configs.length) {
      configs.forEach(cfg => {
        if (cfg.component && cfg.component.name) {
          const component = Vue.component(cfg.component.name);
          if (component && cfg.entryOptions && cfg.entryOptions.route) {
            const routeComponent = {
              // @ts-ignore
              children: component.options ? component.options.hubRoutes : [],
              component: {
                data: () => {
                  return {
                    componentName: cfg.component.name,
                    componentProps: Object.assign({}, cfg.component.props, {
                      displayType: HubElementDisplayTypes.CONTENT,
                      parentPath: nodePath.join(
                        this.baseUrl,
                        cfg.entryOptions.route
                      )
                    })
                  };
                },
                template: `<component :is="componentName" v-bind="componentProps"></component>`
              },
              path: nodePath.join(this.baseUrl, cfg.entryOptions.route)
            };
            routes.push(routeComponent);
          }
        }
      });
    }
    return routes;
  }
  // endregion methods
}
