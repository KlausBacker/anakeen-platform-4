export default class SmartElement {
  protected id!: string | number;
  protected title!: string;
  protected initid!: number;
  protected icon!: string;
  protected name!: string;
  protected revision!: number;
  protected status!: string;

  public changeState() {
    console.log("change state");
  }

  public updateValues() {
    console.log("update values");
  }

  public getPropertyValue() {
    console.log("get property value");
  }

  public getSmartFieldValue() {
    console.log("get smart field value");
  }

  public destroy() {
    console.log("destroy");
  }
}
