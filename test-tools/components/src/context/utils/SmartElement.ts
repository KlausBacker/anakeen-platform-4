import Credentials from "./Credentials";

const fetch = require("node-fetch");

export default class SmartElement {
  protected id!: string | number;
  protected title!: string;
  protected initid!: number;
  protected icon!: string;
  protected name!: string;
  protected revision!: number;
  protected status!: string;
  protected details!: any;

  constructor(smartElementProperties: any) {
    this.id = smartElementProperties.id;
    this.title = smartElementProperties.title;
    this.initid = smartElementProperties.initid;
    this.icon = smartElementProperties.icon;
    this.name = smartElementProperties.name;
    this.revision = smartElementProperties.revision;
    this.status = smartElementProperties.status;
    this.details = Object.assign({}, smartElementProperties);
  }

  public changeState() {
    console.log("change state");
  }

  public updateValues() {
    console.log("update values");
  }

  public getPropertyValue(propertyName: string) {
    return this.details[propertyName];
  }

  public getSmartFieldValue() {
    console.log("get smart field value");
  }

  public destroy() {
    console.log("destroy");
  }
}
