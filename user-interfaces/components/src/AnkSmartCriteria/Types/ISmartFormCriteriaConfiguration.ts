import { ISmartFormFieldItem } from "../../AnkSmartForm/ISmartForm";
import { ICriteriaAutocomplete } from "./IConfigurationCriteria";

export default interface ISmartFormCriteriaConfiguration extends ISmartFormFieldItem {
  originalName?: string;
  autocomplete?: ICriteriaAutocomplete;
}
