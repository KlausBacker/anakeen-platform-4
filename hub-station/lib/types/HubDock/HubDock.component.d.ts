import { Vue } from "vue-property-decorator";
import { DockPosition } from "../HubStation/HubStationsTypes";
export default class HubDock extends Vue {
    get faCollapseIcon(): string;
    get sizeConverted(): string;
    get dockStyle(): object;
    get dockWrapperStyle(): object;
    private static getHubEntriesInstance;
    $vuebar: any;
    position: DockPosition;
    expandable: boolean;
    expanded: boolean;
    collapsedSize: string | number;
    size: string | number;
    collapseOnSelection: boolean;
    superposeDock: boolean;
    expandOnHover: boolean;
    superposeOnHover: boolean;
    hoverDelay: number;
    multiselection: boolean;
    content: object[];
    animate: boolean;
    collapsed: boolean;
    collapsable: boolean;
    superposable: boolean;
    currentSize: string | number;
    selectedItems: object[];
    hubEntries: Vue[];
    $refs: {
        dockEl: HTMLElement;
        dockContent: HTMLElement;
    };
    protected overTimer: number;
    onCollapsed(val: boolean): void;
    onSelectedItems(val: any): void;
    mounted(): void;
    expand(): void;
    collapse(): void;
    toggleDock(): void;
    protected onDockEntryEvent(eventName: any): (eventOption: any) => void;
    protected onOverDock(): void;
    protected onLeaveDock(): void;
    protected setDockWrapperAbsoluteSize(size?: any): void;
}