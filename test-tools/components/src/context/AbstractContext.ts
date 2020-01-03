/* eslint-disable no-unused-vars */
import fetch, { RequestInit as FetchRequestInit } from "node-fetch";
import Account from "./utils/Account";
// eslint-disable-next-line no-unused-vars
import Credentials from "./utils/Credentials";
import SmartElement from "./utils/SmartElement";

export interface ISmartElementProps {
  [key: string]: any;
}

export interface ISmartElementValues {
  [fieldId: string]: { value: string; displayValue: string; [other: string]: any } | string;
}

export interface IAccountData {
  type?: "user" | "role" | "group";
  login: string;
  roles?: string[];
}

export default abstract class AbstractContext {
  protected credentials!: Credentials;
  constructor(credentials: Credentials) {
    this.credentials = credentials;
  }

  public async getAccount(login: string): Promise<Account> {
    const response = await this.fetchApi(`/api/v2/test-tools/account/${login}/`);
    const responseJson = await response.json();
    if (responseJson.success && responseJson.data) {
      return new Account(responseJson.data, (url, ...args) => this.fetchApi(url, ...args));
    } else {
      let msg: string = "unknown error";
      if (responseJson.success === false) {
        msg = responseJson.message;
      }
      throw new Error(`Unable to get login ${login}: ${msg}`);
    }
  }

  // eslint-disable-next-line no-unused-vars
  public async getSmartElement(seName: string) {
    const response = await this.fetchApi(
      `/api/v2/smart-elements/${seName}.json?fields=document.properties.all,document.attributes.all`
    );
    const responseJson = await response.json();

    if (responseJson.success && responseJson.data && responseJson.data.document) {
      return new SmartElement(responseJson.data.document, (url, ...args) => this.fetchApi(url, ...args));
    } else {
      let msg: string = "unknown error";
      if (responseJson.success === false) {
        msg = responseJson.message;
        throw new Error(`unable to get SE ${seName}: ${msg}`);
      }

      throw new Error(responseJson);
    }
  }

  public abstract async clean();

  protected async fetchApi(url: string, init?: FetchRequestInit) {
    let headers = this.credentials.getBasicHeader();
    if (init && init.headers) {
      headers = Object.assign({}, this.credentials.getBasicHeader(), init.headers);
    }
    let fetchOptions = Object.assign({}, init || {}, { headers });
    return await fetch(this.credentials.getCompleteUrl(url), fetchOptions);
  }
}
