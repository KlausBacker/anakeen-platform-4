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

  public async createAccount({ type = "user", login = "", roles = [] }: IAccountData): Promise<Account | undefined> {
    const url = SimpleContext.ACCOUNT_CREATION_API;
    const userInfo = { type, login, roles };
    const requestData: { [key: string]: any } = Object.assign({}, userInfo);
    requestData.tag = this.testTagUid;
    const response = await this.fetchApi(url, {
      body: JSON.stringify(requestData),
      headers: {
        "Content-Type": "application/json"
      },
      method: "post"
    });
    const responseJson = await response.json();
    // console.log(url, requestData, JSON.stringify(responseJson, null, 2));
    if (responseJson.success && responseJson.data) {
      return new Account(responseJson.data, (url, ...args) => this.fetchApi(url, ...args));
    } else {
      let msg: string = "unknown error";
      if (responseJson.success === false) {
        msg = responseJson.message || responseJson.exceptionMessage || responseJson.error;
      }
      throw new Error(`Unable to get login ${userInfo.login}: ${msg} : ${url}`);
    }
  }

  public async createSmartElement(seName: ISmartElementProps, seValues?: ISmartElementValues) {
    // Creation
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
        let msg: string = "unknown error";

        if (responseJson.success === false) {
          msg = responseJson.message;
          throw new Error(`unable to create SE ${seName}: ${msg}`);
        }
      }
    }
  }

  public async clean() {
    const url = SimpleContext.CLEAN_API.replace(/%s/g, this.testTagUid);
    const response = await this.fetchApi(url, {
      headers: {
        "Content-Type": "application/json"
      },
      method: "delete"
    });
    // const responseJson = await response.json();
    // console.log(JSON.stringify(response, null, 2));
    return response;
  }
}
