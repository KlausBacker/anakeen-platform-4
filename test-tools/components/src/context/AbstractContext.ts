import fetch from "node-fetch";
import Account from "./utils/Account";
// eslint-disable-next-line no-unused-vars
import Credentials from "./utils/Credentials";
import SmartElement from "./utils/SmartElement";

export interface ISmartElementProps {
  [key: string]: any;
}

export interface ISmartElementValues {
  [key: string]: any;
}

export default abstract class AbstractContext {
  protected credentials!: Credentials;
  constructor(credentials: Credentials) {
    this.credentials = credentials;
  }

  public getAccount(login: string): Account {
    return new Account().logAs(login);
  }

  public getSmartElement(seName: string | ISmartElementProps, seValues?: ISmartElementValues) {
    return fetch(
      `${this.credentials.uri}/api/v2/smart-elements/${seName}.json?fields=document.properties.all,document.attributes.all`,
      {
        headers: this.credentials.getBasicHeader()
      }
    )
      .then(response => response.json())
      .then(response => {
        if (response.success && response.data && response.data.document) {
          return new SmartElement(response.data.document);
        } else {
          throw new Error("Unfound Smart Element data");
        }
      });
  }

  public abstract clean();
}
