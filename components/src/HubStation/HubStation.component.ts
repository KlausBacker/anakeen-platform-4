import VueAxiosPlugin from "@anakeen/internal-components/lib/AxiosPlugin";
// Vue class based component export
import { Component, Prop, Vue, Watch } from "vue-property-decorator";
import { HubElementDisplayTypes } from "../HubElement/HubElementTypes";
import VueSetupPlugin from "../utils/VueSetupPlugin";
import Router from "./HubRouter";
import HubStationDock from "./HubStationDock/HubStationDock.vue";
import { IHubStationConfig } from "./HubStationsTypes";

Vue.use(VueSetupPlugin);

import {
  DockPosition,
  IHubStationDockConfigs,
  IHubStationPropConfig
} from "./HubStationsTypes";

const urlJoin = require("url-join");

Vue.use(VueAxiosPlugin);

@Component({
  components: {
    "hub-station-dock": HubStationDock
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

  get routeEntries(): object[] {
    let routeEntries: IHubStationPropConfig[] = [];
    if (this.configData) {
      Object.keys(this.configData).forEach(key => {
        const configs = this.configData[key];
        routeEntries = routeEntries.concat(
          configs.filter(cfg => {
            return (
              cfg.component &&
              cfg.component.name &&
              cfg.entryOptions &&
              cfg.entryOptions.route
            );
          })
        );
      });
    }
    routeEntries.map(item => {
      item.entryOptions.completeRoute = urlJoin(
        this.rootUrl,
        item.entryOptions.route
      );
    });
    return routeEntries;
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
    [key: string]: HubStationDock | any;
  };

  // region props
  @Prop({ default: () => ({}), type: Object })
  public config!: IHubStationConfig;
  @Prop({ default: "", type: String }) public baseUrl!: string;
  @Prop({ default: true, type: Boolean }) public withDefaultRouter!: boolean;
  @Prop({ default: false, type: Boolean }) public injectTag!: boolean;
  // endregion props

  public activeRoute: string | null = null;

  protected defaultRoute: { priority: number | null; route: string } = {
    priority: null,
    route: ""
  };
  protected alreadyVisited: object = {};
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
    if (this.$refs[ref] && this.$refs[ref].$refs.innerDock) {
      this.$refs[ref].$refs.innerDock.expand();
    }
  }

  public collapseDock(dockPosition: DockPosition) {
    const ref = `dock${HubStation.capitalize(dockPosition)}`;
    if (this.$refs[ref] && this.$refs[ref].$refs.innerDock) {
      this.$refs[ref].$refs.innerDock.contract();
    }
  }

  public created() {
    this.$_hubEventBus.on("hubNotify", notification => {
      this.$emit("hubNotify", notification);
    });
  }

  public mounted() {
    this.configData = HubStation.organizeData(this.config.hubElements || []);
    if (this.withDefaultRouter) {
      this.initRouterConfig(this.configData);
    }
  }

  // region watch
  @Watch("config")
  protected onConfigPropChanged(val: IHubStationConfig) {
    this.configData = HubStation.organizeData(val.hubElements);
    if (this.withDefaultRouter) {
      this.initRouterConfig(this.configData);
    }
  }

  @Watch("activeRoute")
  protected onActiveRouteChanged(val: string) {
    // reaffect alreadyVisited to make it reactive for vue
    this.alreadyVisited = Object.assign({}, this.alreadyVisited, {
      [val]: true
    });
  }

  protected initRouterConfig(configData: IHubStationDockConfigs) {
    Vue.use(Router);
    Object.keys(configData).forEach(key => {
      const routes = this.getRoutesConfigs(configData[key]);
      if (routes && routes.length) {
        routes.forEach(route => {
          // @ts-ignore
          this.$ankHubRouter.internal.on(route.pattern, route.handler);
        });
      }
    });
    if (this.defaultRoute && this.defaultRoute.route) {
      this.$ankHubRouter.internal.on(this.rootUrl, () => {
        this.$ankHubRouter.internal.navigate(this.defaultRoute.route, true);
        this.$ankHubRouter.internal.resolve(window.location.pathname);
      });
    }
    this.$ankHubRouter.internal.resolve(window.location.pathname);
  }

  protected onHubElementSelected(event) {
    if (
      event &&
      event.entryOptions &&
      event.entryOptions.route &&
      this.withDefaultRouter
    ) {
      const fullRoutePath =
        urlJoin(this.rootUrl, event.entryOptions.route) + "/";
      this.$ankHubRouter.internal.navigate(fullRoutePath, true);
      this.$ankHubRouter.internal.resolve(window.location.pathname);
    }
    this.$emit("hubElementSelected", event);
  }

  private isPriorityDefaultRoute(entryOptions, computedPriority) {
    return (
      (entryOptions.activated === true &&
        this.defaultRoute.priority === null) ||
      (entryOptions.activated === true &&
        // @ts-ignore
        computedPriority > this.defaultRoute.priority)
    );
  }

  private getRoutesConfigs(configs: IHubStationPropConfig[]) {
    const routes: Array<{
      pattern: string | RegExp;
      handler: (params, query) => void;
    }> = [];
    if (configs && configs.length) {
      configs.forEach(cfg => {
        if (cfg.component && cfg.component.name) {
          const component = Vue.component(cfg.component.name);
          if (component && cfg.entryOptions && cfg.entryOptions.route) {
            const absoluteRoute =
              urlJoin(this.rootUrl, cfg.entryOptions.route) + "/";
            const priority =
              cfg.entryOptions.activatedOrder === null ||
              cfg.entryOptions.activatedOrder === undefined
                ? Number.NEGATIVE_INFINITY
                : cfg.entryOptions.activatedOrder;
            if (this.isPriorityDefaultRoute(cfg.entryOptions, priority)) {
              this.defaultRoute.priority = priority;
              this.defaultRoute.route = absoluteRoute;
            }
            routes.push({
              handler: () => {
                this.activeRoute = cfg.entryOptions.route;
              },
              pattern: new RegExp("^" + absoluteRoute)
            });
          }
        }
      });
    }
    if (routes.length) {
      return routes.reverse();
    }
    return null;
  }

  // endregion methods
}
