import fetch from "node-fetch";
import Account from "./utils/Account";
// eslint-disable-next-line no-unused-vars
import Credentials from "./utils/Credentials";
import SmartElement from "./utils/SmartElement";

export interface ISmartElementProps {
  [key: string]: any;
}

export interface ISmartElementValues {
  [fieldId: string]: { value: string, displayValue: string, [other: string]: any };
}

export interface IAccountData {
  type: string,
  login: string,
  roles: string[]
}

export default abstract class AbstractContext {
  protected credentials!: Credentials;
  constructor(credentials: Credentials) {
    this.credentials = credentials;
  }

  public async getAccount(login: string | IAccountData) {
    if (typeof login === "string") {
      const response = await fetch(
        `${this.credentials.uri}/api/v2/test-tools/account/${login}/`,
        {
          headers: this.credentials.getBasicHeader()
        }
      );
      const responseJson = await response.json();
      if (responseJson.success && responseJson.data) {
        return new Account(responseJson.data);
      } else {
        throw new Error("Unfound Smart Element data");
      }
    }
  }

  // eslint-disable-next-line no-unused-vars
  public async getSmartElement(seName: string | ISmartElementProps, seValues?: ISmartElementValues) {
    if (typeof seName === "string") {
      const response = await fetch(
        `${this.credentials.uri}/api/v2/smart-elements/${seName}.json?fields=document.properties.all,document.attributes.all`,
        {
          headers: this.credentials.getBasicHeader()
        }
      );
      const responseJson = await response.json();
      if (responseJson.success && responseJson.data && responseJson.data.document) {
        return new SmartElement(responseJson.data.document);
      } else {
        throw new Error("Unfound Smart Element data");
      }
    }
  }

  public abstract clean();
}
