export interface IHubConfiguration {
  hubElements: HubElementsEntity[];
  instanceName: string;
  routerEntry: string;
  globalAssets: Assets;
}
export interface HubElementsEntity {
  assets: Assets;
  position: Position;
  component: Component;
  entryOptions: EntryOptions;
}
export interface Assets {
  js?: string[];
  css?: string[];
}
export interface Position {
  order?: number;
  dock: string;
  innerPosition: string;
}
export interface Component {
  name: string;
  props?: Props | null;
  internal?: boolean;
}
export interface Props {
  collections?: CollectionsEntity[] | null;
  welcomeTab: boolean;
  iconTemplate: string;
  hubLabel: string;
}
export interface CollectionsEntity {
  title: string;
  initid: string;
  id: string;
  name: string;
  icon: string;
  displayIcon: string;
}
export interface EntryOptions {
  internal: boolean;
  name: string;
  loadingTimeout: number;
  activated: boolean;
  activatedOrder?: number | null;
  selectable: boolean;
  expandable: boolean;
  route: string;
  completeRoute: string;
}