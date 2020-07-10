import SmartCriteriaConfigurationLoader from "@anakeen/user-interfaces/components/src/AnkSmartCriteria/SmartCriteriaConfigurationLoader";
import IConfigurationCriteria from "@anakeen/user-interfaces/components/src/AnkSmartCriteria/Types/IConfigurationCriteria";

export default class SmartCriteriaFulltextConfigurationLoader extends SmartCriteriaConfigurationLoader {
  protected prepareCustomRequest(criteriaConfiguration: IConfigurationCriteria): void {
    super.prepareCustomRequest(criteriaConfiguration);
    criteriaConfiguration.label = criteriaConfiguration.label ? criteriaConfiguration.label : "Recherche générale";
  }
}
