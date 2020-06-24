import SmartFormConfigurationBuilder from "@anakeen/user-interfaces/components/src/AnkSmartCriteria/SmartFormConfigurationBuilder";
import IConfigurationCriteria from "@anakeen/user-interfaces/components/src/AnkSmartCriteria/Types/IConfigurationCriteria";
import { ISmartFormFieldSet } from "@anakeen/user-interfaces/components/src/AnkSmartForm/ISmartForm";
import { SmartCriteriaFulltextKind } from "./Types/SmartCriteriaFulltextKind";

export default class SmartFormFulltextConfigurationBuilder extends SmartFormConfigurationBuilder {
  protected buildCustomCriteriaTemplate(criteria: IConfigurationCriteria, index: number): ISmartFormFieldSet {
    // @ts-ignore
    if (criteria.kind === SmartCriteriaFulltextKind.FULLTEXT) {
      const formTemplate: ISmartFormFieldSet = {
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
      return formTemplate;
    }
  }
}
