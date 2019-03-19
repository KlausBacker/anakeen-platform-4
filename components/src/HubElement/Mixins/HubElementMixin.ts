// mixin.js
const urlJoin = require("url-join");
import Navigo from "navigo";
import { Component, Prop, Vue } from "vue-property-decorator";
import { IHubStationEntryOptions } from "../../HubStation/HubStationsTypes";
import VueSetupPlugin from "../../utils/VueSetupPlugin";
import { HubElementDisplayTypes } from "../HubElementTypes";

Vue.use(VueSetupPlugin);

// You can declare a mixin as the same style as components.
@Component
export default class HubElementMixin extends Vue {
  get isDockCollapsed() {
    return this.displayType === HubElementDisplayTypes.COLLAPSED;
  }

  get isDockExpanded() {
    return this.displayType === HubElementDisplayTypes.EXPANDED;
  }

  get isHubContent() {
    return this.displayType === HubElementDisplayTypes.CONTENT;
  }
  @Prop() public entryOptions!: IHubStationEntryOptions;
  @Prop() public displayType!: HubElementDisplayTypes;
  @Prop() public parentPath!: string;

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

  public navigate(to: string) {
    const router = this.getRouter();
    if (router !== null) {
      router.navigate(to, true);
    }
  }

  public hubNotify(notification = {}) {
    // @ts-ignore
    if (this.$_hubEventBus) {
      this.$_hubEventBus.emit("hubNotify", notification);
    }
  }

  protected getRouter(): Navigo | null {
    if (this.$ankHubRouter) {
      return this.$ankHubRouter.external;
    }
    return null;
  }
}
