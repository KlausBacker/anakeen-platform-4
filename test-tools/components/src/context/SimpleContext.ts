import uuid = require("uuid/v4");
// eslint-disable-next-line no-unused-vars
import AbstractContext, { ISmartElementProps, ISmartElementValues, IAccountData } from "./AbstractContext";
// eslint-disable-next-line no-unused-vars
import Credentials from "./utils/Credentials";
import SmartElement from "./utils/SmartElement";
import Account from "./utils/Account";

export default class SimpleContext extends AbstractContext {
  private static CREATION_API: string = "/api/v2/test-tools/smart-structures/%s/smart-elements/";
  private static ACCOUNT_CREATION_API: string = "/api/v2/test-tools/accounts/";
  private static CLEAN_API: string = "/api/v2/test-tools/context/%s/";

  protected testTagUid!: string;

  constructor(credentials: Credentials) {
    super(credentials);
    this.testTagUid = uuid();
  }

  public async getAccount(login: string | IAccountData) {
    if (typeof login === "string") {
      return super.getAccount(login);
    } else {
      const url = SimpleContext.ACCOUNT_CREATION_API;
      if (login && typeof login === "object") {
        const requestData: { [key: string]: any } = Object.assign({}, login);
        requestData.tag = this.testTagUid;
        const response = await this.fetchApi(url, {
          body: JSON.stringify(requestData),
          headers: {
            "Content-Type": "application/json"
          },
          method: "post"
        });
        const responseJson = await response.json();
        if (responseJson.success && responseJson.data) {
          return new Account(responseJson.data, (url, ...args) => this.fetchApi(url, ...args));
        } else {
          let msg: string = 'unknown error';
          if(responseJson.success === false) {
            msg = responseJson.message || responseJson.exceptionMessage;
          }
          throw new Error(`unable to get login ${login}: ${msg}`);
        }
      }
    }
  }

  public async getSmartElement(seName: string | ISmartElementProps, seValues?: ISmartElementValues) {
    if (typeof seName === "string") {
      return super.getSmartElement(seName);
    } else {
      // Creation or get (UPSERT mode)
      const ssName = seName.smartStructure;
      const requestData = { document: { attributes: {}, options: { tag: "" } } };
      if (ssName) {
        const url = SimpleContext.CREATION_API.replace(/%s/g, ssName);
        if (seValues && typeof seValues === "object") {
          requestData.document.attributes = seValues;
        }
        requestData.document.options.tag = this.testTagUid;
        const response = await this.fetchApi(url, {
          body: JSON.stringify(requestData),
          headers: {
            "Content-Type": "application/json"
          },
          method: "post"
        });
        const responseJson = await response.json();
        if (responseJson.success && responseJson.data && responseJson.data.document) {
          return new SmartElement(responseJson.data.document, (url, ...args) => this.fetchApi(url, ...args));
        } else {
          let msg: string = 'unknown error';
          if(responseJson.success === false && !seValues) {
            msg = responseJson.message;
            throw new Error(`unable to get SE ${seName}: ${msg}`);
          }
          if(responseJson.success === false && seValues) {
            msg = responseJson.message;
            throw new Error(`unable to create SE ${seName}: ${msg}`);
          }
        }
      }
    }
  }

  public async clean() {
    const url = SimpleContext.CLEAN_API.replace(/%s/g, this.testTagUid);
    const response = await this.fetchApi(url, {
      headers: {
        "Content-Type": "application/json",
      },
      method: "delete"
    });
    const responseText = await response.text();
  }
}
