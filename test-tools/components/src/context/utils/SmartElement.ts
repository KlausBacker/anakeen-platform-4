export default class SmartElement {
  protected properties: any;
  protected smartFields: any;

  constructor(smartElementData) {
    this.properties = smartElementData.properties;
    this.smartFields = smartElementData.attributes;
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
