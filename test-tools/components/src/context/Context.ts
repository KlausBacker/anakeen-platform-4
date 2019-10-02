import Account from "./utils/Account";
import fetch from "node-fetch";
import Credentials from "./utils/Credentials";
import SmartElement from "./utils/SmartElement";

export default class Context {
  protected credentials: Credentials;
  protected safeMode: boolean = false;


  constructor(url: string, login: string, password: string) {
    this.credentials = new Credentials(url, login, password);
    /* fetch(this.credentials.uri, {
      headers: {
        Authorization: this.credentials.getBasicHeader()
      }
    })
      .then((res: any) => res.json())
      .then((data: any) => {
        console.log(data);
      })
      .catch((err) => {
        console.error(err);
        throw err;
      }); */
  }

  public initTest(safeMode: boolean = false) {
    this.safeMode = safeMode;
    return this;

  }

  public getSmartElement(logicalName: string): Promise<SmartElement> {
    return fetch(`${this.credentials.uri}api/v2/smart-elements/${logicalName}.json?fields=document.properties.security,document.properties.creationDate`, {
      headers: {
        "Authorization": this.credentials.getBasicHeader()
      }
    })
    .then((response) => {
      return response.json();
    })
    .then(response => {
      return new SmartElement(response.data.document.properties);
    });
  }

  public getAccount(login: string): Account {
    return new Account().logAs(login);
  }
}
