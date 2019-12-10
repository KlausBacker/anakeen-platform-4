import HubStationDock from "./HubStationDock/HubStationDock.vue";
import { DockPosition, IHubStationConfig, IHubStationDockConfigs, IHubStationPropConfig } from "./HubStationsTypes";
declare const HubStation_base: import("vue-class-component/lib/declarations").VueClass<unknown>;
export default class HubStation extends HubStation_base {
    $ankHubRouter: any;
    $_hubEventBus: any;
    get isHeaderEnabled(): number;
    get isFooterEnabled(): number;
    get isLeftEnabled(): number;
    get isRightEnabled(): number;
    get DockPosition(): any;
    get HubElementDisplayTypes(): any;
    get rootUrl(): string;
    get routeEntries(): object[];
    private static capitalize;
    private static organizeData;
    rootHubStation: this;
    configData: IHubStationDockConfigs;
    $refs: {
        [key: string]: HubStationDock | any;
    };
    config: IHubStationConfig;
    baseUrl: string;
    withDefaultRouter: boolean;
    injectTag: boolean;
    activeRoute: string;
    panes: IHubStationPropConfig[];
    protected defaultRoute: {
        priority: number | null;
        route: string;
    };
    protected alreadyVisited: object;
    addHubElement(config: IHubStationPropConfig): void;
    expandDock(dockPosition: any): void;
    collapseDock(dockPosition: DockPosition): void;
    created(): void;
    mounted(): void;
    render(createElement: any): any;
    protected onConfigPropChanged(val: IHubStationConfig): void;
    protected onActiveRouteChanged(val: string): void;
    protected initRouterConfig(configData: IHubStationDockConfigs): void;
    protected onHubElementSelected(event: any): void;
    private isPriorityDefaultRoute;
    private getRoutesConfigs;
}
export {};
