import { ISmartElementValues } from "../AbstractContext";

export default class SmartElement {
  private static UPDATE_API: string = "/api/v2/test-tools/smart-elements/%s/";
  protected properties: any;
  protected smartFields: any;

  private fetchApi: any = null;

  constructor(smartElementData, fetch?: any) {
    this.properties = smartElementData.properties;
    this.smartFields = smartElementData.attributes;
    this.fetchApi = fetch;
  }

  public changeState() {
    console.log("change state");
  }

  public async updateValues(seValues: ISmartElementValues) {
    const url = SmartElement.UPDATE_API.replace(/%s/g, this.properties.id);
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

  public getPropertyValue(propertyName: string) {
    return this.properties[propertyName];
  }

  public getValue(fieldId: string) {
    return this.smartFields[fieldId];
  }

  public destroy() {
    console.log("destroy");
  }
}
