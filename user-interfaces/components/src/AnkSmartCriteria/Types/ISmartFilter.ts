import { SmartCriteriaKind } from "./SmartCriteriaKind";
import { SmartFilterLogic } from "./SmartFilterLogic";
import IFilter from "./IFilter";

export default interface ISmartFilter extends IFilter {
  kind: SmartCriteriaKind;
  logic: SmartFilterLogic;
  customData?: any;
  filters: Array<ISmartFilter>;
}
