import ContextBase from "./ContextBase";
import IContext from "./IContext";
import Credentials from "./utils/Credentials";
import SafeContext from "./SafeContext";

export default class Context extends ContextBase {
  protected credentials!: Credentials;
  protected safeMode: boolean = false;

  constructor(url: string, login: string, password: string) {
    this.credentials = new Credentials(url, login, password);
    return fetch(url, {
      headers: this.credentials.getBasicHeader()
    }).then(() => {
      return this;
    });
  }

  public initTest(safeMode: boolean = false): IContext {
    this.safeMode = safeMode;
    if (this.safeMode) {
      return new SafeContext(this.credentials);
    } else {
      return new SimpleContext(this.credentials);
    }
  }

  public clean() {}
}
