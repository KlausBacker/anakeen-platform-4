// eslint-disable-next-line no-unused-vars
import { ISmartElementValue } from "../AnkSmartElement/ISmartElementValue";
export interface ISmartFormValue extends ISmartElementValue {
  formConfiguration?: ISmartFormConfiguration;
}

export interface ISmartFormConfiguration {
  title?: string;
  type?: string;
  icon?: string;
  structure?: object;
  renderOptions?: object;
  values?: object;
}
