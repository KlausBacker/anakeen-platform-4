import { ICriteriaOperator } from "./ICriteriaOperator";

/**
 * Unitary structure of a filter
 */
export default interface IFilter {
  field?: string | number;
  operator: ICriteriaOperator;
  value: any;
  displayValue: any;
  customData?: any;
  id: string;
}
