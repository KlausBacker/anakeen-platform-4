import { ISmartElementValues } from "../AbstractContext";

interface ITestOptions {
  login?: string,
  dryRun?: boolean,
  searchParams?: object
}

interface StateInfos {
  transition: string,
  askValues?: object
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

  public searchParams(options?: ITestOptions) {
    const searchParams = new URLSearchParams();
    if (options && options.login) {
      searchParams.set('login', options.login);
    }
    if (options && options.dryRun) {
      searchParams.set('dry-run', options.dryRun.toString());
    }
    if(options && options.searchParams) {
      for (let searchParam in options.searchParams) {
        searchParams.set(searchParam, options.searchParams[searchParam]);
      }
    }
    return searchParams;
  }

  public async changeState(stateInfo: StateInfos, options?: ITestOptions): Promise<SmartElement> {
    const baseUrl = SmartElement.CHANGE_API.replace(/<docid>/g, this.properties.initid).replace(/<transition>/g, stateInfo.transition);
    const searchParams = this.searchParams(options);
    const url = `${baseUrl}?${searchParams}`
    const response = await this.fetchApi(url, {
      headers: {
        "Content-Type": "application/json"
      },
      method: "put",
      body: JSON.stringify(stateInfo.askValues)
    });
    const responseJson = await response.json();
    if (responseJson.success && responseJson.data && responseJson.data.document) {
      return new SmartElement(responseJson.data.document, (url, ...args) => this.fetchApi(url, ...args));
    } else {
      let msg: string = 'unknown error';
      if(responseJson.success === false) {
        msg = responseJson.message || responseJson.exceptionMessage;
      }
      throw new Error(`unable to change state ${stateInfo}: ${msg}`);
    }
  }

  public async setState(newState: string, options?: ITestOptions): Promise<SmartElement> {
    const baseUrl = SmartElement.SET_API.replace(/<docid>/g, this.properties.initid).replace(/<state>/g, newState);
    const searchParams = this.searchParams(options);
    const url = `${baseUrl}?${searchParams}`
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
      let msg: string = 'unknown error';
      if(responseJson.success === false) {
        msg = responseJson.message;
      }
      throw new Error(`unable to set state ${newState}: ${msg}`);
    }
  }

  public async updateValues(seValues: ISmartElementValues, options?: ITestOptions): Promise<SmartElement> {
    const baseUrl = SmartElement.UPDATE_API.replace(/<docid>/g, this.properties.initid);
    const searchParams = this.searchParams(options);
    const url = `${baseUrl}?${searchParams}`
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
      let msg: string = 'unknown error';
      if(responseJson.success === false) {
        msg = responseJson.message || responseJson.exceptionMessage;
      }
      throw new Error(`unable to update value for ${seValues}: ${msg}`);
    }
  }

  public async getPropertyValue(propertyName: string, options?: ITestOptions): Promise<any> {
    const searchParams = this.searchParams(options);
    searchParams.set('fields', `document.properties.${propertyName}`);
    const url = `${SmartElement.BASE_API}smart-elements/${this.properties.initid}.json?${searchParams}`;
    const response = await this.fetchApi(url);
    const responseJson = await response.json();
    if (responseJson.success && responseJson.data && responseJson.data.document) {
      return responseJson.data.document.properties[propertyName];
    } else {
      let msg: string = 'unknown error';
      if(responseJson.success === false) {
        msg = responseJson.message;
      }
      throw new Error(`unable to get value for property ${propertyName}: ${msg}`);
    }
  }

  public async getValue(fieldId: string, options?: ITestOptions): Promise<{ value: any, displayValue: string }> {
    const searchParams = this.searchParams(options);
    searchParams.set('fields', `document.attributes.${fieldId}`);
    const url = `${SmartElement.BASE_API}smart-elements/${this.properties.initid}.json?${searchParams}`;
    const response = await this.fetchApi(url);
    const responseJson = await response.json();
    if (responseJson.success && responseJson.data && responseJson.data.document) {
      return responseJson.data.document.attributes[fieldId];
    } else {
      let msg: string = 'unknown error';
      if(responseJson.success === false) {
        msg = responseJson.message;
      }
      throw new Error(`unable to get value for attribute ${fieldId}: ${msg}`);
    }
  }

  public async getValues(options?: ITestOptions): Promise<{ [fieldId: string]: any }> {
    const searchParams = this.searchParams(options);
    searchParams.set('fields', `document.attributes.all`);
    const response = await this.fetchApi(
      `/api/v2/smart-elements/${this.properties.initid}.json?${searchParams}`
    );
    const responseJson = await response.json();

    if (responseJson.success && responseJson.data && responseJson.data.document) {
      return responseJson.data.document.attributes;
    } else {
      let msg: string = 'unknown error';
      if(responseJson.success === false) {
        msg = responseJson.message;
      }
      throw new Error(`unable to get all attributes : ${msg}`);
    }
  }

  public async getPropertiesValues(options?: ITestOptions): Promise<{ [fieldId: string]: any }> {
    const searchParams = this.searchParams(options);
    searchParams.set('fields', `document.properties.all`);
    const response = await this.fetchApi(
      `/api/v2/smart-elements/${this.properties.initid}.json?${searchParams}`
    );
    const responseJson = await response.json();

    if (responseJson.success && responseJson.data && responseJson.data.document) {
      return responseJson.data.document.properties;
    } else {
      let msg: string = 'unknown error';
      if(responseJson.success === false) {
        msg = responseJson.message;
      }
      throw new Error(`unable to get all values : ${msg}`);
    }
  }

  public async destroy(options?: ITestOptions): Promise<SmartElement> {
    const searchParams = this.searchParams(options);
    const url = `${SmartElement.UPDATE_API.replace(/<docid>/g, this.properties.initid)}?${searchParams}`;
    // console.log(url);
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
      let msg: string = 'unknown error';
      if(responseJson.success === false) {
        msg = responseJson.message;
      }
      throw new Error(`unable to destroy SE : ${msg}`);
    }
  }
}
