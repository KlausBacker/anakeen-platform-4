import Navigo from "navigo";
import { Vue } from "vue-property-decorator";
import { IHubStationEntryOptions } from "../../HubStation/HubStationsTypes";
export default class HubElementMixin extends Vue {
    entryOptions: IHubStationEntryOptions;
    isDockCollapsed: boolean;
    parentPath: string;
    $_hubEventBus: any;
    $ankHubRouter: any;
    $store: any;
    resolveHubSubPath(subPath: any): string;
    registerRoute(route: any, routeCallback: any): Navigo | undefined;
    registerRoutes(routesHandler: any): Navigo | undefined;
    navigate(to: string, absolute?: boolean, options?: {
        silent: boolean;
    }): void;
    hubNotify(notification?: {}): void;
    getRouter(): Navigo | null;
    getStore(): any;
}
