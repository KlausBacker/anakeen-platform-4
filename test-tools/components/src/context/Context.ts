// eslint-disable-next-line no-unused-vars
import fetch from "node-fetch";
// eslint-disable-next-line no-unused-vars
import AbstractContext from "./AbstractContext";
import SafeContext from "./SafeContext";
import SimpleContext from "./SimpleContext";
import Credentials from "./utils/Credentials";

export default class Context {
  protected credentials!: Credentials;
  protected safeMode: boolean = false;

  constructor(url: string, login: string, password: string) {
    this.credentials = new Credentials(url, login, password);
    // @ts-ignore
    return fetch(url, {
      headers: this.credentials.getBasicHeader()
    })
      .then(() => {
        return this;
      })
      .catch(e => {
        let msg: string;
        switch (e.code) {
          case "ECONNREFUSED":
            msg = `Context initialization failed: Connection to '${url}' refused`;
            break;
          default:
            msg = `Context initialization failed: unknown error for '${url}': ${e.message}`;
        }
        throw new Error(msg);
      });
  }

  public initTest(safeMode: boolean = false): AbstractContext {
    this.safeMode = safeMode;
    if (this.safeMode) {
      return new SafeContext(this.credentials);
    } else {
      return new SimpleContext(this.credentials);
    }
  }

  public clean() {}
}
