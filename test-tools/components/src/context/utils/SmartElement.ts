export default class SmartElement {
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

  public updateValues() {
    console.log("update values");
  }

  public getPropertyValue(propertyName: string) {
    return this.properties[propertyName];
  }

  public getSmartFieldValue(fieldId: string) {
    return this.smartFields[fieldId];
  }

  public destroy() {
    console.log("destroy");
  }
}
