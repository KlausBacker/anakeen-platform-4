// mixin.js
const urlJoin = require("url-join");
import Navigo from "navigo";
import { Component, Prop, Vue } from "vue-property-decorator";
import { IHubStationEntryOptions } from "../../HubStation/HubStationsTypes";
import { HubElementDisplayTypes } from "../HubElementTypes";

// You can declare a mixin as the same style as components.
@Component({
  inject: ["$_hubStation"]
})
export default class HubElementMixin extends Vue {
  @Prop() public entryOptions!: IHubStationEntryOptions;
  @Prop() public displayType!: HubElementDisplayTypes;
  @Prop() public parentPath!: string;

  get isDockCollapsed() {
    return this.displayType === HubElementDisplayTypes.COLLAPSED;
  }

  get isDockExpanded() {
    return this.displayType === HubElementDisplayTypes.EXPANDED;
  }

  get isHubContent() {
    return this.displayType === HubElementDisplayTypes.CONTENT;
  }

  public resolveHubSubPath(subPath) {
    return urlJoin(this.parentPath, subPath);
  }

  public hubNotify(notification = {}) {
    // @ts-ignore
    this.$_hubStation.$emit("hubNotify", notification);
  }

  public registerRoute(route, routeCallback) {
    const router = this.getRouter();
    if (router !== null) {
      router.on(route, routeCallback);
    }
  }

  public registerRoutes(routesHandler) {
    const router = this.getRouter();
    if (router !== null) {
      router.on(routesHandler);
    }
  }

  public navigate(to) {
    const router = this.getRouter();
    if (router !== null) {
      router.navigate(to, true);
    }
  }

  protected getRouter(): Navigo | null {
    if (this.$ankHubRouter) {
      return this.$ankHubRouter;
    }
    return null;
  }
}
