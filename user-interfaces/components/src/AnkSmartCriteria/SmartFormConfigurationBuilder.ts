import ISmartCriteriaConfiguration from "./Types/ISmartCriteriaConfiguration";
import IConfigurationCriteria, { ICriteriaConfigurationOperator } from "./Types/IConfigurationCriteria";
import {
  ISmartFormConfiguration,
  ISmartFormFieldEnumItem,
  ISmartFormFieldItem,
  ISmartFormFieldSet
} from "../AnkSmartForm/ISmartForm";
import { SmartCriteriaKind } from "./Types/SmartCriteriaKind";
import SmartCriteriaUtils from "./SmartCriteriaUtils";
import ISmartFormCriteriaConfiguration from "./Types/ISmartFormCriteriaConfiguration";

export default class SmartFormConfigurationBuilder {
  configuration: ISmartCriteriaConfiguration;
  smartFormConfiguration: ISmartFormConfiguration;
  errorStack: Array<any>;
  translations: any;
  responsiveColumns: any[];
  fieldTranslationMap: any;

  constructor(
    smartCriteriaConfiguration: ISmartCriteriaConfiguration,
    translations: any,
    responsiveColumnns: Array<any>
  ) {
    this.configuration = smartCriteriaConfiguration;
    this.errorStack = [];
    this.translations = translations;
    this.responsiveColumns = responsiveColumnns;
    this.fieldTranslationMap = {};
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

    this.buildSmartFormAutocompleteFields(this.smartFormConfiguration);
    return this.smartFormConfiguration;
  }

  private addCriteriaTemplateToSmartForm(criteria: IConfigurationCriteria): void {
    const index = this.smartFormConfiguration.structure[0].content.length;
    let formTemplate: ISmartFormFieldSet;
    switch (criteria.kind) {
      case SmartCriteriaKind.PROPERTY:
      case SmartCriteriaKind.VIRTUAL:
      case SmartCriteriaKind.FIELD:
        formTemplate = this.buildDefaultCriteriaTemplate(criteria, index);
        break;
      default:
        formTemplate = this.buildCustomCriteriaTemplate(criteria, index);
        break;
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
          if (criteria.multipleFilter) {
            this.smartFormConfiguration.values[SmartFormConfigurationBuilder.getValueMultipleName(index)] =
              criteria.default.value;
          } else {
            this.smartFormConfiguration.values[SmartFormConfigurationBuilder.getValueName(index)] =
              criteria.default.value;
          }
        }
      }
    }
  }

  private static getCriteriaSmartFormValue(
    criteria: IConfigurationCriteria,
    index: number,
    multipleFilter: boolean,
    between = false
  ): ISmartFormFieldSet | ISmartFormFieldItem | ISmartFormFieldEnumItem {
    const name = between
      ? SmartFormConfigurationBuilder.getValueBetweenName(index)
      : multipleFilter
      ? SmartFormConfigurationBuilder.getValueMultipleName(index)
      : SmartFormConfigurationBuilder.getValueName(index);
    const label = criteria.label;
    const formValue: ISmartFormCriteriaConfiguration = {
      name,
      originalName: criteria.field,
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

    // Autocomplete
    if (criteria.autocomplete) {
      formValue.autocomplete = JSON.parse(JSON.stringify(criteria.autocomplete));
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
      if (operator.options && operator.options.length > 0) {
        operator.key = operator.key + "%" + operator.options.join("%");
      }
    });
    return operators;
  }

  /**
   * Method used to build smart form configuration from smart criteria custom kind (i.e. fulltext)
   * To extend functionnalities, this method can be overriden.
   * @param criteria the criteria parameter
   * @param index of the criteria
   */
  protected buildCustomCriteriaTemplate(criteria: IConfigurationCriteria, index: number): ISmartFormFieldSet {
    return { name: "", type: undefined };
  }

  /**
   * Build the smart form template for default kind
   * @param criteria the criteria parameter
   * @param index of the criteria
   */
  private buildDefaultCriteriaTemplate(criteria: IConfigurationCriteria, index: number): ISmartFormFieldSet {
    const hasBetween = SmartCriteriaUtils.hasBetweenOperator(criteria);
    const operators = SmartFormConfigurationBuilder.buildSmartFormOperators(criteria.operators);

    // Content and values
    const formTemplate: ISmartFormFieldSet = {
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
        SmartFormConfigurationBuilder.getCriteriaSmartFormValue(criteria, index, false),
        SmartFormConfigurationBuilder.getCriteriaSmartFormValue(criteria, index, true)
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
      formTemplate.content.push(SmartFormConfigurationBuilder.getCriteriaSmartFormValue(criteria, index, false, true));
      this.smartFormConfiguration.values[
        SmartFormConfigurationBuilder.getValueBetweenLabelName(index)
      ] = this.translations.and;
    }

    // Render Options
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
      this.smartFormConfiguration.renderOptions["fields"][SmartFormConfigurationBuilder.getValueBetweenName(index)] = {
        labelPosition: "none"
      };
    }
    return formTemplate;
  }

  /**
   * Sets autocompletes to be readable
   * @param smartFormConfiguration
   */
  private buildSmartFormAutocompleteFields(smartFormConfiguration: ISmartFormConfiguration) {
    this.fieldTranslationMap = {};

    // build matching fields map
    const criteriaFrames = smartFormConfiguration.structure[0].content;
    for (const criteriaFrame of criteriaFrames) {
      // @ts-ignore
      const criteriaFields = criteriaFrame.content;
      for (const criteriaField of criteriaFields) {
        const originalName = criteriaField.originalName;
        if (originalName) {
          const multiple = criteriaField.multiple ? "multiple" : "single";
          if (!this.fieldTranslationMap[originalName]) {
            this.fieldTranslationMap[originalName] = {};
          }
          this.fieldTranslationMap[originalName][multiple] = criteriaField.name;
        }
      }
    }

    for (const criteriaFrame of criteriaFrames) {
      // @ts-ignore
      const criteriaFields = criteriaFrame.content;
      for (const criteriaField of criteriaFields) {
        const autocomplete = criteriaField.autocomplete;
        if (autocomplete) {
          const multiple = criteriaField.multiple ? "multiple" : "single";
          if (autocomplete.outputs) {
            autocomplete.outputs = this.getTranslatedKeysObject(
              autocomplete.outputs,
              this.fieldTranslationMap,
              multiple
            );
          }
          if (autocomplete.inputs) {
            autocomplete.inputs = this.getTranslatedKeysObject(autocomplete.inputs, this.fieldTranslationMap, multiple);
          }
        }
      }
    }
  }

  /**
   * Translates keys given a map and its multiplicity
   * @param object
   * @param map
   * @param multiple
   */
  private getTranslatedKeysObject(object, map, multiple) {
    const translatedObject = {};
    Object.keys(object).forEach(key => {
      if (map[key] && map[key][multiple]) {
        translatedObject[map[key][multiple]] = object[key];
      } else {
        translatedObject[key] = object[key];
      }
    });
    return translatedObject;
  }
}
