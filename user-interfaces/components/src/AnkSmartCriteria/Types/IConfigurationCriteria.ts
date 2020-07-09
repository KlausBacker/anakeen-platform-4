import { SmartCriteriaKind } from "./SmartCriteriaKind";
import { ISmartFormFieldEnumConfig } from "../../AnkSmartForm/ISmartForm";
import { CriteriaAdditionalOptions, CriteriaOperator, CriteriaOption, ICriteriaOperator } from "./ICriteriaOperator";

export default interface IConfigurationCriteria {
  kind: SmartCriteriaKind;
  label: string;
  operators: Array<ICriteriaConfigurationOperator>;
  multipleFilter: boolean;
  default: {
    operator?: ICriteriaConfigurationOperator;
    value?: any;
  };
  field?: string;
  structure?: string | number;
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
  multipleField?: boolean;
  valueHtmlId?: string;
  operatorHtmlId?: string;
  enumItems?: ISmartFormFieldEnumConfig[];
  typeFormat?: string;
  customData?: any;
  modifiableOperator?: boolean;

  // For state property
  stateStructure?: string;
  stateWorkflow?: number | string;
  stateList?: Array<ISmartFormFieldEnumConfig>;
  searchDomain?: string;

  // Autocomplete
  autocomplete?: ICriteriaAutocomplete;
}

export interface ICriteriaConfigurationOperator extends ISmartFormFieldEnumConfig {
  // Data from server
  acceptValues?: boolean;
  isBetween?: boolean;
  filterMultiple?: boolean;

  // Data from user configuration
  options: CriteriaOption[];
  additionalOptions: CriteriaAdditionalOptions[];
  key: CriteriaOperator;
}

export interface ICriteriaAutocomplete {
  url?: string;
  inputs?: any;
  outputs?: any;
}
