export interface SEListCollection {
  success: boolean;
  data: Data;
}

export interface Data {
  requestParameters: RequestParameters;
  uri: string;
  documents: Document[];
  collection: Collection;
  resultMax: number;
  paginationState: PaginationState;
  user: User;
}

export interface Collection {
  properties: CollectionProperties;
  uri: string;
}

export interface CollectionProperties {
  id: number;
  title: string;
  initid: number;
}

export interface Document {
  properties: DocumentProperties;
  uri: string;
}

export interface DocumentProperties {
  id: number;
  title: string;
  initid: number;
  icon: string;
  name: null;
  revision: number;
  state: State;
  status: string;
}

export interface State {
  reference: string;
  color: string;
  activity: string;
  stateLabel: string;
  displayValue: string;
}

export interface PaginationState {
  page: number;
  slice: number;
  total_entries: number;
}

export interface RequestParameters {
  slice: number;
  offset: number;
  length: number;
  orderBy: string;
}

export interface User {
  id: number;
  fid: number;
}
