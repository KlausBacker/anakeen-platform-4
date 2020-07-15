import { Component } from "vue-property-decorator";
import AnkSmartCriteria from "@anakeen/user-interfaces/components/src/AnkSmartCriteria/AnkSmartCriteria.component";
import AnkSmartCriteriaVueComponent from "@anakeen/user-interfaces/components/lib/AnkSmartCriteria.esm";
import ISmartCriteriaConfiguration from "@anakeen/user-interfaces/components/src/AnkSmartCriteria/Types/ISmartCriteriaConfiguration";
import SmartFormFulltextConfigurationBuilder from "./SmartFormFulltextConfigurationBuilder";
import ISmartFilter from "@anakeen/user-interfaces/components/src/AnkSmartCriteria/Types/ISmartFilter";
import IConfigurationCriteria from "@anakeen/user-interfaces/components/src/AnkSmartCriteria/Types/IConfigurationCriteria";

@Component({
  name: "ank-fulltext-smart-criteria",
  extends: AnkSmartCriteriaVueComponent
})
export default class AnkFulltextSmartCriteria extends AnkSmartCriteria {
  protected getLoaderUrl(): string {
    return "/api/v2/smartcriteria/fulltext/loadconfiguration";
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
