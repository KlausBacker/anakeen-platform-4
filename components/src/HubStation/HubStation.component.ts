import { AnkNotifier, VueAxiosPlugin } from "@anakeen/internal-components";

const nodePath = require("path");
import AnkComponents from "@anakeen/user-interfaces";
// Vue class based component export
import { Component, Prop, Vue, Watch } from "vue-property-decorator";
import HubDock from "../HubDock/HubDock.vue";
import HubDockEntry from "../HubDock/HubDockEntry/HubDockEntry.vue";
import { HubElementDisplayTypes } from "../HubElement/HubElementTypes";
import HubLabel from "../HubLabel/HubLabel.vue";
import { IHubStationConfig } from "./HubStationsTypes";

import {
  DockPosition,
  IAnkDock,
  IHubStationDockConfigs,
  IHubStationPropConfig,
  InnerDockPosition
} from "./HubStationsTypes";

Vue.use(VueAxiosPlugin);
Vue.use(AnkComponents, { globalVueComponents: true });
Vue.component("hub-label", HubLabel);
@Component({
  components: {
    "ank-notifier": AnkNotifier,
    "hub-dock": HubDock,
    "hub-dock-entry": HubDockEntry
  }
})
export default class HubStation extends Vue {
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

  // noinspection JSMethodCanBeStatic
  get DockPosition(): any {
    return DockPosition;
  }

  // noinspection JSMethodCanBeStatic
  get HubElementDisplayTypes(): any {
    return HubElementDisplayTypes;
  }

  get rootUrl(): string {
    if (this.config && this.config.routerEntry) {
      return this.config.routerEntry;
    } else if (this.baseUrl) {
      return this.baseUrl;
    }
    return "";
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
    [key: string]: IAnkDock | any;
  };

  // region props
  @Prop({ default: () => ({}), type: Object })
  public config!: IHubStationConfig;
  @Prop({ default: "", type: String }) public baseUrl!: string;
  @Prop({ default: true, type: Boolean }) public withNotifier!: boolean;
  @Prop({ default: false, type: Boolean }) public injectTag!: boolean;
  // endregion props

  // region watch
  @Watch("config")
  public onConfigPropChanged(val: IHubStationConfig) {
    this.configData = HubStation.organizeData(val.hubElements);
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
      return nodePath.join(this.rootUrl, entryOptions.route);
    }
    return "";
  }

  // noinspection JSMethodCanBeStatic
  public resizeWindow() {
    // Need deferred because of animation
    window.setTimeout(() => {
      window.dispatchEvent(new Event("resize"));
    }, 1000);
  }
  // noinspection JSMethodCanBeStatic
  public isSelectableEntry(entry) {
    if (entry && entry.entryOptions) {
      return entry.entryOptions.selectable;
    }
    return true;
  }

  public created() {
    if (this.$http && this.$http.errorEvents) {
      this.$http.errorEvents.on("error", event => {
        event.defaultPrevented = false;
        event.preventDefault = function() {
          this.defaultPrevented = true;
        };
        this.$emit("hubError", event);
        if (this.withNotifier && !event.defaultPrevented) {
          this.$refs.ankNotifier.publishNotification(
            new CustomEvent("ankNotification", {
              detail: [
                {
                  content: event.message,
                  title: event.title,
                  type: "error"
                }
              ]
            })
          );
        }
      });
    }
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
                        this.rootUrl,
                        cfg.entryOptions.route
                      )
                    })
                  };
                },
                template: `<component :is="componentName" v-bind="componentProps"></component>`
              },
              path: nodePath.join(this.rootUrl, cfg.entryOptions.route)
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
