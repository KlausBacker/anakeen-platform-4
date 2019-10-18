import { ISmartElementValues } from "../AbstractContext";
import Account from "../utils/Account";
import { IOptions } from "minimatch";

interface ITestOptions {
  login?: string
}

export default class SmartElement {
  private static BASE_API: string = "/api/v2/test-tools/";
  private static UPDATE_API: string = SmartElement.BASE_API + "smart-elements/<docid>/";
  private static SET_API: string = SmartElement.BASE_API + "smart-elements/<docid>/workflows/states/<state>/";
  private static CHANGE_API: string = SmartElement.BASE_API + "smart-elements/<docid>/workflows/transitions/<transition>/";
  protected properties: any;
  protected smartFields: any;

  private fetchApi: any = null;

  constructor(smartElementData, fetch?: any) {
    this.properties = smartElementData.properties;
    this.smartFields = smartElementData.attributes;
    this.fetchApi = fetch;
  }

  public async changeState(stateInfo: { transition: string, ask: any, account?: Account }): Promise<SmartElement> {
    const url = SmartElement.CHANGE_API.replace(/<docid>/g, this.properties.initid).replace(/<transition>/g, stateInfo.transition);
    const response = await this.fetchApi(url, {
      headers: {
        "Content-Type": "application/json"
      },
      method: "put"
    });
    const responseJson = await response.json();
    if (responseJson.success && responseJson.data && responseJson.data.document) {
      return new SmartElement(responseJson.data.document, (url, ...args) => this.fetchApi(url, ...args));
    } else {
      throw new Error("Unfound Smart Element data");
    }
  }

  public async setState(newState: string): Promise<SmartElement> {
    const url = SmartElement.SET_API.replace(/<docid>/g, this.properties.initid).replace(/<state>/g, newState);
    const response = await this.fetchApi(url, {
      headers: {
        "Content-Type": "application/json"
      },
      method: "put"
    });
    const responseJson = await response.json();
    if (responseJson.success && responseJson.data && responseJson.data.document) {
      return new SmartElement(responseJson.data.document, (url, ...args) => this.fetchApi(url, ...args));
    } else {
      throw new Error("Unfound Smart Element data");
    }
  }

  public async updateValues(seValues: ISmartElementValues): Promise<SmartElement> {
    const url = SmartElement.UPDATE_API.replace(/<docid>/g, this.properties.initid);
    const response = await this.fetchApi(url, {
      body: JSON.stringify(seValues),
      headers: {
        "Content-Type": "application/json"
      },
      method: "put"
    });
    const responseJson = await response.json();
    if (responseJson.success && responseJson.data && responseJson.data.document) {
      return new SmartElement(responseJson.data.document, (url, ...args) => this.fetchApi(url, ...args));
    } else {
      throw new Error("Unfound Smart Element data");
    }
  }

  public async getPropertyValue(propertyName: string, options?: ITestOptions): Promise<any> {
    const searchParams = new URLSearchParams();
    searchParams.set('fields', `document.properties.${propertyName}`);
    if (options && options.login) {
      searchParams.set('login', options.login);
    }
    const response = await this.fetchApi(
      `${SmartElement.BASE_API}smart-elements/${this.properties.initid}.json?${searchParams}`
    );
    const responseJson = await response.json();
    if (responseJson.success && responseJson.data && responseJson.data.document) {
      return responseJson.data.document.properties[propertyName];
    } else {
      throw new Error("Unfound Smart Element data");
    }
  }

  public async getValue(fieldId: string): Promise<{ value: any, displayValue: string }> {
    const response = await this.fetchApi(
      `/api/v2/smart-elements/${this.properties.initid}.json?fields=document.attributes.${fieldId}`
    );
    const responseJson = await response.json();

    if (responseJson.success && responseJson.data && responseJson.data.document) {
      return responseJson.data.document.attributes[fieldId];
    } else {
      throw new Error("Unfound Smart Element data");
    }
  }

  public async getValues(): Promise<{ [fieldId: string]: any }> {
    const response = await this.fetchApi(
      `/api/v2/smart-elements/${this.properties.initid}.json?fields=document.attributes.all`
    );
    const responseJson = await response.json();

    if (responseJson.success && responseJson.data && responseJson.data.document) {
      return responseJson.data.document.attributes;
    } else {
      throw new Error("Unfound Smart Element data");
    }
  }

  public async getPropertiesValues(): Promise<{ [fieldId: string]: any }> {
    const response = await this.fetchApi(
      `/api/v2/smart-elements/${this.properties.initid}.json?fields=document.properties.all`
    );
    const responseJson = await response.json();

    if (responseJson.success && responseJson.data && responseJson.data.document) {
      return responseJson.data.document.properties;
    } else {
      throw new Error("Unfound Smart Element data");
    }
  }

  public async destroy(): Promise<SmartElement> {
    const url = SmartElement.UPDATE_API.replace(/<docid>/g, this.properties.initid);
    const response = await this.fetchApi(url, {
      headers: {
        "Content-Type": "application/json"
      },
      method: "delete"
    });
    const responseJson = await response.json();
    if (responseJson.success && responseJson.data && responseJson.data.document) {
      return new SmartElement(responseJson.data.document, (url, ...args) => this.fetchApi(url, ...args));
    } else {
      throw new Error("Unfound Smart Element data");
    }
  }
}
