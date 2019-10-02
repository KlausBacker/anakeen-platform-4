const base64 = require("base-64");

class Credentials {
  public uri: string;
  public login: string;
  public password: string;
  constructor(uri: string, login: string, password: string) {
    this.uri = uri;
    this.login = login;
    this.password = password;
  }

  public getBasicHeader() {
    return `Basic ${base64.encode(this.login + ":" + this.password)}`;
  }
}

export default Credentials;
