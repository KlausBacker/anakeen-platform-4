import {Vue} from "vue/types/vue";

export enum DockPosition {
    TOP = "TOP", BOTTOM = "BOTTOM", LEFT = "LEFT", RIGHT = "RIGHT", CENTER = "CENTER"
}

export type HubStationConfigPosition = {
    dock: DockPosition,
    innerPosition: DockPosition
}

type HubStationConfigComponentDef = {
    name: string,
    props: object
}

export type HubStationDockConfigs = {
    top: HubStationPropConfig[],
    bottom: HubStationPropConfig[],
    left: HubStationPropConfig[],
    right: HubStationPropConfig[]
};

export type HubStationPropConfig = {
    position: HubStationConfigPosition,
    component: HubStationConfigComponentDef
}

export interface IAnkDock extends Vue {
    expand(): void;
    contract(): void;
}