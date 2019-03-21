export interface IAuthenticationToken {
  token?: string;
  user?: string;
  author?: string;
  description?: string;
  creationDate?: Date;
  expirationDate?: Date;
  expendable?: boolean;
  routes?: IAuthenticationTokenRoute[];
}

export interface IAuthenticationTokenRoute {
  pattern: string;
  method: string;
}

export interface IAuthenticationTokenDescription {
  user: string;
  description: string;
  expirationDate: string;
  expendable: boolean;
  routes: IAuthenticationTokenRoute[];
}
