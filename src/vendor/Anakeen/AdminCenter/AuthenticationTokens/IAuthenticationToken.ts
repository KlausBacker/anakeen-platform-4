export default interface IAuthenticationToken {
  token?: string;
  user?: string;
  author?: string;
  description?: string;
  creationDate?: Date;
  expirationDate?: Date;
}
