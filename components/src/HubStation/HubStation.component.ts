const nodePath = require("path");
import HubDock from "../HubDock/HubDock.vue";
import HubDockEntry from "../HubDock/HubDockEntry/HubDockEntry.vue";
import {
  DockPosition,
  HubStationDockConfigs,
  HubStationPropConfig,
  IAnkDock,
  InnerDockPosition
} from "./HubStationsTypes";
// Vue class based component export
import {Component, Prop, Vue, Watch} from "vue-property-decorator";
import AnkComponents from "@anakeen/ank-components";
import {HubElementDisplayTypes} from "../HubElement/HubElementTypes";
Vue.use(AnkComponents, { globalVueComponents: true });
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
    this.initRouterConfig(this.configData);
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

  get HubElementDisplayTypes(): any {
      return HubElementDisplayTypes;
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
      return c.position.innerPosition === InnerDockPosition.HEADER;
    })
  }

  getDockContent(configs: HubStationPropConfig[]) {
    return configs.filter(c => {
      return c.position.innerPosition === InnerDockPosition.CENTER;
    })
  }

  getDockFooter(configs: HubStationPropConfig[]) {
    return configs.filter(c => {
      return c.position.innerPosition === InnerDockPosition.FOOTER;
    })
  }

  initRouterConfig(configData: HubStationDockConfigs) {
      Object.keys(configData).forEach(key => {
         const routes = this._getRoutesConfigs(configData[key]);
         if (this.$router) {
             this.$nextTick(() => {
                 this.$router.addRoutes(routes);
             });
         }
      });
  }

  getEntryRoutePath(path) {
      return nodePath.join(this.baseUrl, path);
  }

  mounted() {
      this.initRouterConfig(this.configData);
  }

  private _getRoutesConfigs(configs: HubStationPropConfig[]) {
      const routes: any[] = [];
      if (configs && configs.length) {
          configs.forEach(cfg => {
              if (cfg.component && cfg.component.name) {
                  const component = Vue.component(cfg.component.name);
                  if (component && cfg.entryOptions && cfg.entryOptions.route) {
                      const routeComponent = {
                          path: nodePath.join(this.baseUrl, cfg.entryOptions.route),
                          component: {
                              template: `<component :is="componentName" v-bind="componentProps"></component>`,
                              data: () => {
                                  return {
                                      componentName: cfg.component.name,
                                      componentProps: Object.assign({}, cfg.component.props,
                                          {
                                              displayType: HubElementDisplayTypes.CONTENT,
                                              iconTemplate: cfg.entryOptions.iconTemplate,
                                              parentPath: nodePath.join(this.baseUrl, cfg.entryOptions.route)
                                          })
                                  }
                              }
                          },
                          // @ts-ignore
                          children: component.options ? component.options.hubRoutes : []
                      };
                      routes.push(routeComponent);
                  }
              }
          });
      }
      return routes;
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