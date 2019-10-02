/* tslint:disable:variable-name */
import base64 = require("base-64");

export default class Credentials {
  private readonly _uri!: string;
  private readonly _login!: string;
  private readonly _password!: string;

  constructor(uri: string, login: string, password: string) {
    this._uri = uri;
    this._login = login;
    this._password = password;
  }

  get uri(): string {
    return this._uri;
  }

  get login(): string {
    return this._login;
  }

  get password(): string {
    return this._password;
  }

  public getBasicHeader() {
    return {
      Authorization: `Basic ${base64.encode(this._login + ":" + this._password)}`
    };
  }

  public getCompleteUrl(uri: string) {
    return `${this.uri}/${uri}`.replace(/(?<!http:)\/\/+/g, "/");
  }
}
