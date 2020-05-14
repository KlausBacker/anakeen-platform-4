import ISmartCriteriaConfiguration from "./Types/ISmartCriteriaConfiguration";
import IConfigurationCriteria, { ICriteriaConfigurationOperator } from "./Types/IConfigurationCriteria";
import {
  ISmartFormConfiguration,
  ISmartFormFieldSet,
  ISmartFormFieldItem,
  ISmartFormFieldEnumItem
} from "../AnkSmartForm/ISmartForm";
import { SmartCriteriaKind } from "./Types/SmartCriteriaKind";
import SmartCriteriaUtils from "./SmartCriteriaUtils";

export default class SmartFormConfigurationBuilder {
  configuration: ISmartCriteriaConfiguration;
  smartFormConfiguration: ISmartFormConfiguration;
  errorStack: Array<any>;
  translations: any;
  responsiveColumns: any[];

  constructor(
    smartCriteriaConfiguration: ISmartCriteriaConfiguration,
    translations: any,
    responsiveColumnns: Array<any>
  ) {
    this.configuration = smartCriteriaConfiguration;
    this.errorStack = [];
    this.translations = translations;
    this.responsiveColumns = responsiveColumnns;
    this.initSmartFormConfiguration();
  }

  private initSmartFormConfiguration(): void {
    this.smartFormConfiguration = {};
    if (this.configuration.title) {
      this.smartFormConfiguration.title = this.configuration.title;
    }
    this.smartFormConfiguration.structure = [
      {
        name: "sc_tab",
        type: "tab",
        content: []
      }
    ];
    this.smartFormConfiguration.values = {};
    this.smartFormConfiguration.renderOptions = {
      fields: {
        sc_tab: {
          responsiveColumns: this.responsiveColumns
        }
      }
    };
  }

  build(): ISmartFormConfiguration {
    this.configuration.criterias.map(criteria => this.addCriteriaTemplateToSmartForm(criteria));
    return this.smartFormConfiguration;
  }

  private addCriteriaTemplateToSmartForm(criteria: IConfigurationCriteria): void {
    const index = this.smartFormConfiguration.structure[0].content.length;
    let formTemplate: ISmartFormFieldSet;

    //Fulltext
    if (criteria.kind === SmartCriteriaKind.FULLTEXT) {
      formTemplate = {
        content: [
          {
            label: criteria.label,
            name: SmartFormConfigurationBuilder.getValueName(index),
            type: "text"
          }
        ],
        type: "frame",
        name: `sc_criteria_${index}`
      };
      this.smartFormConfiguration.renderOptions["fields"][SmartFormConfigurationBuilder.getValueName(index)] = {
        displayDeleteButton: false
      };
      if (criteria.default) {
        this.smartFormConfiguration.values[SmartFormConfigurationBuilder.getValueName(index)] = criteria.default.value;
      }

      //Others
    } else {
      const hasBetween = SmartCriteriaUtils.hasBetweenOperator(criteria);
      const operators = SmartFormConfigurationBuilder.buildSmartFormOperators(criteria.operators);
      formTemplate = {
        content: [
          {
            name: SmartFormConfigurationBuilder.getOperatorLabelName(index),
            type: "text",
            display: "read"
          },
          {
            label: criteria.label,
            name: SmartFormConfigurationBuilder.getOperatorName(index),
            type: "enum",
            enumItems: operators
          },
          SmartFormConfigurationBuilder.getCriteriaSmartFormValue(criteria, index, false, this.translations),
          SmartFormConfigurationBuilder.getCriteriaSmartFormValue(criteria, index, true, this.translations)
        ],
        type: "frame",
        name: `sc_criteria_${index}`
      };
      this.smartFormConfiguration.values[SmartFormConfigurationBuilder.getOperatorLabelName(index)] = criteria.label;
      if (hasBetween) {
        formTemplate.content.push({
          name: SmartFormConfigurationBuilder.getValueBetweenLabelName(index),
          type: "text",
          display: "read"
        });
        formTemplate.content.push(
          SmartFormConfigurationBuilder.getCriteriaSmartFormValue(criteria, index, false, this.translations, true)
        );
        this.smartFormConfiguration.values[
          SmartFormConfigurationBuilder.getValueBetweenLabelName(index)
        ] = this.translations.and;
      }
      this.smartFormConfiguration.renderOptions["fields"][SmartFormConfigurationBuilder.getOperatorLabelName(index)] = {
        labelPosition: "none"
      };
      this.smartFormConfiguration.renderOptions["fields"][SmartFormConfigurationBuilder.getOperatorName(index)] = {
        displayDeleteButton: false,
        labelPosition: "none"
      };
      this.smartFormConfiguration.renderOptions["fields"][SmartFormConfigurationBuilder.getValueName(index)] = {
        labelPosition: "none"
      };
      this.smartFormConfiguration.renderOptions["fields"][SmartFormConfigurationBuilder.getValueMultipleName(index)] = {
        labelPosition: "none"
      };
      if (hasBetween) {
        this.smartFormConfiguration.renderOptions["fields"][
          SmartFormConfigurationBuilder.getValueBetweenLabelName(index)
        ] = {
          labelPosition: "none"
        };
        this.smartFormConfiguration.renderOptions["fields"][
          SmartFormConfigurationBuilder.getValueBetweenName(index)
        ] = {
          labelPosition: "none"
        };
      }
    }
    this.smartFormConfiguration.structure[0].content.push(formTemplate);
    this.smartFormConfiguration.renderOptions["fields"][`sc_criteria_${index}`] = {
      responsiveColumns: [
        {
          number: 4,
          minWidth: "0rem",
          maxWidth: "200rem",
          grow: true
        }
      ],
      labelPosition: "none"
    };
    if (criteria.default) {
      let isDefaultBetween = false;
      if (criteria.default.operator) {
        if (!criteria.default.operator.options) {
          criteria.default.operator.options = [];
        }
        const fetchedOperator = SmartCriteriaUtils.getOperatorDataFromOperator(criteria.default.operator, criteria);
        if (fetchedOperator != null) {
          criteria.default.operator = fetchedOperator;
          isDefaultBetween = criteria.default.operator.isBetween;
          this.smartFormConfiguration.values[SmartFormConfigurationBuilder.getOperatorName(index)] =
            criteria.default.operator.key;
        } else {
          this.stackError(
            `Error: Default operator value '${criteria.default.operator.key}' cannot be found and will be ignored`,
            "warning"
          );
        }
      }
      if (criteria.default.value) {
        if (isDefaultBetween) {
          if (Array.isArray(criteria.default.value)) {
            if (criteria.default.value.length >= 2) {
              this.smartFormConfiguration.values[SmartFormConfigurationBuilder.getValueName(index)] =
                criteria.default.value[0];
              this.smartFormConfiguration.values[SmartFormConfigurationBuilder.getValueBetweenName(index)] =
                criteria.default.value[1];
            }
          } else {
            this.stackError(
              `Error: Default operator value '${criteria.default.operator.key}' must specify a default value with an array of two values`,
              "warning"
            );
          }
        } else {
          this.smartFormConfiguration.values[SmartFormConfigurationBuilder.getValueName(index)] =
            criteria.default.value;
        }
      }
    }
  }

  private static getCriteriaSmartFormValue(
    criteria: IConfigurationCriteria,
    index: number,
    multipleFilter: boolean,
    translations: any,
    between = false
  ): ISmartFormFieldSet | ISmartFormFieldItem | ISmartFormFieldEnumItem {
    const name = between
      ? SmartFormConfigurationBuilder.getValueBetweenName(index)
      : multipleFilter
      ? SmartFormConfigurationBuilder.getValueMultipleName(index)
      : SmartFormConfigurationBuilder.getValueName(index);
    const label = between ? translations.and : "";
    const formValue: ISmartFormFieldSet | ISmartFormFieldItem | ISmartFormFieldEnumItem = {
      name,
      label,
      type: criteria.type,
      multiple: multipleFilter
    };
    switch (criteria.type) {
      case "enum":
        (formValue as ISmartFormFieldEnumItem).enumItems = criteria.enumItems;
        break;
      case "docid":
      case "account":
        formValue.typeFormat = criteria.typeFormat;
        break;
      default:
        break;
    }
    return formValue;
  }

  private stackError(message: string, type = "error"): void {
    this.errorStack.push({ message, type });
  }

  public getErrorStack(): Array<any> {
    return [...this.errorStack];
  }

  public static getOperatorName(index: number): string {
    return `sc_operator_${index}`;
  }

  public static getOperatorLabelName(index: number): string {
    return `sc_label_operator_${index}`;
  }

  public static getValueName(index: number): string {
    return `sc_value_${index}`;
  }

  public static getValueBetweenName(index: number): string {
    return `sc_value_between_${index}`;
  }

  public static getValueBetweenLabelName(index: number): string {
    return `sc_label_value_between_${index}`;
  }

  public static getValueMultipleName(index: number): string {
    return `sc_value_multiple_${index}`;
  }

  private static buildSmartFormOperators(
    operatorsConfig: Array<ICriteriaConfigurationOperator>
  ): Array<ICriteriaConfigurationOperator> {
    const operators = JSON.parse(JSON.stringify(operatorsConfig));
    operators.map(operator => {
      if (operator.options.length > 0) {
        operator.key = operator.key + "%" + operator.options.join("%");
      }
    });
    return operators;
  }
}
