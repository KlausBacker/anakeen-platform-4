import IConfigurationCriteria, { ICriteriaConfigurationOperator } from "./Types/IConfigurationCriteria";
import { CriteriaOperator } from "./Types/ICriteriaOperator";
import { SmartCriteriaKind } from "./Types/SmartCriteriaKind";

export default class {
  public static areOpatorEquals(
    operator1: ICriteriaConfigurationOperator,
    operator2: ICriteriaConfigurationOperator
  ): boolean {
    let equals = false;

    if (!operator1.options) {
      operator1.options = [];
    }
    if (!operator2.options) {
      operator2.options = [];
    }

    if (operator1.key === operator2.key && operator1.options.length === operator2.options.length) {
      const optionIntersection = operator1.options.filter(op1 => {
        return !operator2.options.includes(op1);
      });
      if (optionIntersection.length === 0) {
        equals = true;
      }
    }
    return equals;
  }

  public static getOperatorData(operatorId: string, criteria: IConfigurationCriteria): ICriteriaConfigurationOperator {
    const splitOp = operatorId.split("%");
    const operatorName = splitOp[0];
    splitOp.splice(0, 1).sort();
    const operatorToFind: ICriteriaConfigurationOperator = {
      // @ts-ignore
      options: splitOp,
      additionalOptions: [],
      // @ts-ignore
      key: operatorName,
      label: ""
    };
    return this.getOperatorDataFromOperator(operatorToFind, criteria);
  }

  public static getOperatorDataFromOperator(
    operator: ICriteriaConfigurationOperator,
    criteria: IConfigurationCriteria
  ): ICriteriaConfigurationOperator {
    let result: ICriteriaConfigurationOperator = {
      additionalOptions: [],
      options: [],
      acceptValues: true,
      isBetween: false,
      key: CriteriaOperator.NONE,
      label: "",
      filterMultiple: false
    };
    if (this.isStandardKind(criteria.kind)) {
      result = criteria.operators.find(op => {
        return this.areOpatorEquals(operator, op);
      });
    }
    return result;
  }

  public static hasBetweenOperator(criteria: IConfigurationCriteria): boolean {
    let hasBetween = false;
    for (const operator of criteria.operators) {
      if (operator.isBetween) {
        hasBetween = true;
      }
    }
    return hasBetween;
  }

  /**
   * returns true if kind is standard, false otherwise
   * @param kind
   */
  public static isStandardKind(kind): boolean {
    return Object.values(SmartCriteriaKind).includes(kind);
  }
}
