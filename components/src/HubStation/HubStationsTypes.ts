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
}

export interface IHubStationConfigComponentDef {
  name: string;
  props: object;
}

export interface IHubStationEntryOptions {
  route: string;
  selectable: boolean;
  selected: boolean;
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

export interface IAnkDock extends Vue {
  expand(): void;
  contract(): void;
}
