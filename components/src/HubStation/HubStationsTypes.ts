import { Vue } from "vue/types/vue";

export enum DockPosition {
  TOP = "TOP",
  BOTTOM = "BOTTOM",
  LEFT = "LEFT",
  RIGHT = "RIGHT"
}

export enum InnerDockPosition {
  HEADER = "HEADER",
  CENTER = "CENTER",
  FOOTER = "FOOTER"
}

export interface IHubStationConfigPosition {
  dock: DockPosition;
  innerPosition: InnerDockPosition;
  order: number;
}

export interface IHubStationConfigComponentDef {
  name: string;
  props: object;
}

export interface IHubStationEntryOptions {
  route: string;
  selectable: boolean;
  activated: boolean;
  activatedOrder: number | null;
}

export interface IHubStationDockConfigs {
  top: IHubStationPropConfig[];
  bottom: IHubStationPropConfig[];
  left: IHubStationPropConfig[];
  right: IHubStationPropConfig[];
}

export interface IHubStationPropConfig {
  position: IHubStationConfigPosition;
  component: IHubStationConfigComponentDef;
  entryOptions: IHubStationEntryOptions;
}

interface IHubStationAssets {
  js: string[];
  css: string[];
}

export interface IHubStationConfig {
  instanceName: string;
  routerEntry: string;
  globalAssets: IHubStationAssets;
  hubElements: IHubStationPropConfig[];
}

export interface IAnkDock extends Vue {
  expand(): void;
  contract(): void;
}
