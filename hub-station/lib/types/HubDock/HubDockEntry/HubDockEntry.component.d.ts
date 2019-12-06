import { Vue } from "vue-property-decorator";
export declare const HUB_DOCK_ENTRY_NAME = "HubDockEntry";
export declare const dockEntryEvents: {
    selected: string;
};
export default class HubDockEntry extends Vue {
    evenSpace: boolean;
    name: string;
    selected: boolean;
    selectable: boolean;
    route: string | object;
    entrySelected: boolean;
    hubDock: any;
    onSelectedPropChange(val: any): void;
    get isCollapsed(): boolean;
    get entryStyle(): object;
    get entryConfiguration(): object;
    selectEntry(): void;
}
