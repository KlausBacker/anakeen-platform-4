// eslint-disable-next-line no-unused-vars
import { ISmartElementValue } from "../AnkSmartElement/ISmartElementValue";
export interface ISmartFormValue extends ISmartElementValue {
  formConfiguration?: ISmartFormConfiguration;
}

export interface ISmartFormFieldCommon {
  name: string;
  type: string;
  label?: string;
  display?: "write" | "read" | "none";
}

export interface ISmartFormFieldTopSet extends ISmartFormFieldCommon {
  type: "frame" | "tab";
  content?: ISmartFormStructureContent;
}

export interface ISmartFormFieldSet extends ISmartFormFieldCommon {
  type: "frame" | "array";
  content?: ISmartFormStructureContent;
}

export interface ISmartFormFieldItem extends ISmartFormFieldCommon {
  type:
    | "frame"
    | "array"
    | "text"
    | "enum"
    | "longtext"
    | "image"
    | "file"
    | "date"
    | "integer"
    | "int"
    | "double"
    | "money"
    | "password"
    | "json"
    | "xml"
    | "time"
    | "timestamp"
    | "color"
    | "docid"
    | "htmltext"
    | "account";
  multiple?: boolean;
  needed?: boolean;
  options?: object;
  typeFormat?: string;
}

export interface ISmartFormFieldEnumConfig {
  key: string;
  label: string;
}

export interface ISmartFormFieldEnumItem extends ISmartFormFieldItem {
  type: "enum";
  enumItems: ISmartFormFieldEnumConfig[];
}

export interface ISmartFormStructureContent
  extends Array<ISmartFormFieldSet | ISmartFormFieldItem | ISmartFormFieldEnumItem> {}

export type ISmartFormFieldValue = string | number | ISmartFormFieldCompleteValue;

interface ISmartFormFieldsValues {
  [key: string]: ISmartFormFieldValue | ISmartFormFieldValue[];
}

export interface ISmartFormFieldCompleteValue {
  value: string;
  displayValue: string;
}

export interface ISmartFormMenuCommon {
  id: string;
  beforeContent?: string;
  htmlAttributes?: object;
  type: "listMenu" | "itemMenu" | "separatorMenu";
  label?: string;
  htmlLabel?: string;
  tooltipLabel?: string;
  tooltipPlacement?: ["top" | "bottom" | "right" | "left"];
  tooltipHtml?: boolean;
  visibility?: "visible" | "hidden" | "disabled";
  important?: boolean;
  iconUrl?: string;
}

export interface ISmartFormMenuItem extends ISmartFormMenuCommon {
  url: string;
  target?: string;
  targetOptions?: object;
  confirmationText?: string;
  confirmationOptions?: object;
}

export interface ISmartFormMenuList extends ISmartFormMenuCommon {
  content: ISmartFormMenuElement[];
}

export type ISmartFormMenuElement = ISmartFormMenuItem | ISmartFormMenuList;

export interface ISmartFormConfiguration {
  title?: string;
  type?: string;
  icon?: string;
  structure?: ISmartFormFieldTopSet[];
  renderOptions?: object;
  values?: ISmartFormFieldsValues;
  menu?: ISmartFormMenuElement[];
}
