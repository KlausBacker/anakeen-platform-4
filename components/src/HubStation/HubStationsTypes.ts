import {Vue} from "vue/types/vue";

export enum DockPosition {
    TOP = "TOP", BOTTOM = "BOTTOM", LEFT = "LEFT", RIGHT = "RIGHT"
}

export enum InnerDockPosition {
    HEADER = "HEADER", CENTER = "CENTER", FOOTER = "FOOTER"
}

export type HubStationConfigPosition = {
    dock: DockPosition,
    innerPosition: InnerDockPosition
}

export type HubStationConfigComponentDef = {
    name: string,
    props: object
}

export type HubStationEntryOptions = {
    route: string,
    selectable: boolean,
    selected: boolean
}

export type HubStationDockConfigs = {
    top: HubStationPropConfig[],
    bottom: HubStationPropConfig[],
    left: HubStationPropConfig[],
    right: HubStationPropConfig[]
};

export type HubStationPropConfig = {
    position: HubStationConfigPosition,
    component: HubStationConfigComponentDef,
    entryOptions: HubStationEntryOptions,
}

export interface IAnkDock extends Vue {
    expand(): void;
    contract(): void;
}