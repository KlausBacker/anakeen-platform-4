import SmartFormConfigurationBuilder from "@anakeen/user-interfaces/components/src/AnkSmartCriteria/SmartFormConfigurationBuilder";
import IConfigurationCriteria from "@anakeen/user-interfaces/components/src/AnkSmartCriteria/Types/IConfigurationCriteria";
import { ISmartFormFieldSet } from "@anakeen/user-interfaces/components/src/AnkSmartForm/ISmartForm";
import { SmartCriteriaFulltextKind } from "./Types/SmartCriteriaFulltextKind";
import ISmartCriteriaConfiguration from "@anakeen/user-interfaces/components/src/AnkSmartCriteria/Types/ISmartCriteriaConfiguration";
import SmartCriteriaUtils from "@anakeen/user-interfaces/components/src/AnkSmartCriteria/SmartCriteriaUtils";

export default class SmartFormFulltextConfigurationBuilder extends SmartFormConfigurationBuilder {
  constructor(
    smartCriteriaConfiguration: ISmartCriteriaConfiguration,
    translations: any,
    responsiveColumnns: Array<any>
  ) {
    super(smartCriteriaConfiguration, translations, responsiveColumnns);
  }

  protected buildCustomCriteriaTemplate(criteria: IConfigurationCriteria, index: number): ISmartFormFieldSet {
    // @ts-ignore
    if (criteria.kind === SmartCriteriaFulltextKind.FULLTEXT) {
      const formTemplate: ISmartFormFieldSet = {
        content: [
          {
            label: criteria.label,
            name: SmartCriteriaUtils.getValueName(index),
            type: "text"
          }
        ],
        type: "frame",
        name: `sc_criteria_${index}`
      };
      this.smartFormConfiguration.renderOptions["fields"][SmartCriteriaUtils.getValueName(index)] = {
        displayDeleteButton: false
      };
      if (criteria.default) {
        this.smartFormConfiguration.values[SmartCriteriaUtils.getValueName(index)] = criteria.default.value;
      }
      return formTemplate;
    }
  }
}
