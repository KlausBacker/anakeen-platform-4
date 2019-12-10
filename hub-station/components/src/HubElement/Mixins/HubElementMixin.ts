// mixin.js
import urlJoin from "url-join";
import Navigo from "navigo";
import { Component, Prop, Vue } from "vue-property-decorator";
import { IHubStationEntryOptions } from "../../HubStation/HubStationsTypes";
import VueSetupPlugin from "../../utils/VueSetupPlugin";

Vue.use(VueSetupPlugin);

// You can declare a mixin as the same style as components.
@Component
export default class HubElementMixin extends Vue {
  @Prop() public entryOptions!: IHubStationEntryOptions;
  // @Prop() public displayType!: HubElementDisplayTypes;
  @Prop({ required: true, type: Boolean, default: true })
  public isDockCollapsed!: boolean;
  @Prop() public parentPath!: string;
  $_hubEventBus: any;
  $ankHubRouter: any;
  $store: any;

  public resolveHubSubPath(subPath) {
    return urlJoin(this.parentPath, subPath);
  }

  public registerRoute(route, routeCallback) {
    const router = this.getRouter();
    if (router !== null) {
      return router.on(route, routeCallback);
    }
  }

  public registerRoutes(routesHandler) {
    const router = this.getRouter();
    if (router !== null) {
      return router.on(routesHandler);
    }
  }

  public navigate(to: string, absolute = true, options = { silent: false }) {
    const router = this.getRouter();
    if (router !== null) {
      if (options.silent === true) {
        router.pause();
      }
      router.navigate(to, absolute);
      if (options.silent === true) {
        router.resume();
      }
    }
  }

  public hubNotify(notification = {}) {
    if (this.$_hubEventBus) {
      this.$_hubEventBus.emit("hubNotify", notification);
    }
  }

  public getRouter(): Navigo | null {
    if (this.$ankHubRouter) {
      return this.$ankHubRouter.external;
    }
    return null;
  }

  public getStore(): any {
    // @ts-ignore
    if (this.$store) {
      return this.$store;
    }
    return null;
  }
}
