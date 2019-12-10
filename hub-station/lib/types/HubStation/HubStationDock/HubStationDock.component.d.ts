import { Vue } from "vue-property-decorator";
import HubDock from "../../HubDock/HubDock.vue";
import { DockPosition, IHubStationPropConfig, InnerDockPosition } from "../HubStationsTypes";
export default class HubStationDock extends Vue {
    get InnerDockPosition(): typeof InnerDockPosition;
    get dockState(): boolean;
    get HubElementDisplayTypes(): any;
    get isExpandable(): boolean;
    protected static normalizeUrl(...url: any[]): any;
    readonly rootHubStation: Vue;
    $refs: {
        innerDock: HubDock | any;
    };
    dockContent: IHubStationPropConfig[];
    position: DockPosition;
    rootUrl: string;
    activeRoute: string;
    dockIsCollapsed: boolean;
    mounted(): void;
    protected getDock(type: any, configs: IHubStationPropConfig[]): IHubStationPropConfig[];
    protected resizeWindow(): void;
    protected isSelectableEntry(entry: any): boolean;
    protected isSelectedEntry(entry: any): any;
    protected onEntrySelected(event: any, entry: any): void;
    protected getEntryRoute(entry: any): any;
    protected onComponentMounted(entry: any, ref: any, index: any): void;
}
