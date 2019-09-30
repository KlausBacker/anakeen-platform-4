import Account from "./utils/Account";

export default class Context {
  protected url: string;
  protected login: string;
  protected password: string;

  constructor(url: string, login: string, password: string) {
    this.url = url;
    this.login = login;
    this.password = password;
  }

  public initTest(safeMode: boolean = false) {
    console.log("Safe mode = ", safeMode);
  }

  public getAccount(login: string): Account {
    return new Account().logAs(login);
  }
}
