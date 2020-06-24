import { Component } from "vue-property-decorator";
import AnkSmartCriteria from "@anakeen/user-interfaces/components/src/AnkSmartCriteria/AnkSmartCriteria.component";
import ISmartCriteriaConfiguration from "@anakeen/user-interfaces/components/src/AnkSmartCriteria/Types/ISmartCriteriaConfiguration";
import SmartCriteriaFulltextConfigurationLoader from "./SmartCriteriaFulltextConfigurationLoader";
import SmartFormFulltextConfigurationBuilder from "./SmartFormFulltextConfigurationBuilder";
import ISmartFilter from "@anakeen/user-interfaces/components/src/AnkSmartCriteria/Types/ISmartFilter";
import IConfigurationCriteria from "@anakeen/user-interfaces/components/src/AnkSmartCriteria/Types/IConfigurationCriteria";

@Component({
  name: "ank-fulltext-smart-criteria"
})
export default class AnkFulltextSmartCriteria extends AnkSmartCriteria {
  protected getConfigurationLoader(config: ISmartCriteriaConfiguration) {
    return new SmartCriteriaFulltextConfigurationLoader(config);
  }

  protected getSmartFormConfigurationBuilder(
    innerConfig: ISmartCriteriaConfiguration,
    translations: { and: string },
    responsiveColumns: any
  ) {
    return new SmartFormFulltextConfigurationBuilder(innerConfig, translations, responsiveColumns);
  }

  protected customFilterValueAdditionalProcessing(smartFilter: ISmartFilter, criteria: IConfigurationCriteria): void {
    smartFilter.field = criteria.searchDomain;
    // @ts-ignore
    smartFilter.operator.key = "fulltext";
  }
}
