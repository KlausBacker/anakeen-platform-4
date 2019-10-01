import fetch from "node-fetch";
import Account from "./utils/Account";
import Credentials from "./utils/Credentials";
import SmartElement from "./utils/SmartElement";

export default class ContextBase {
  public getAccount(login: string): Account {
    return new Account().logAs(login);
  }

  public getSmartElement(smartElementName: string) {
    return fetch(
      `${this.credentials.uri}/api/v2/smart-elements/${smartElementName}.json?fields=document.properties.all,document.attributes.all`,
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
}
