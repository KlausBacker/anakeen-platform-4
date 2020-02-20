export interface IDomainConfig {
  name?: string;
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
