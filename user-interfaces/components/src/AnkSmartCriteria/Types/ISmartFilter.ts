import { SmartCriteriaKind } from "./SmartCriteriaKind";
import { SmartFilterLogic } from "./SmartFilterLogic";
import IFilter from "./IFilter";

export default interface ISmartFilter extends IFilter {
  kind: SmartCriteriaKind;
  logic: SmartFilterLogic;
  disabled?: boolean;
  customData?: any;
  filters: Array<ISmartFilter>;
}
