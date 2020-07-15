import IConfigurationCriteria from "./IConfigurationCriteria";

export default interface ISmartCriteriaConfiguration {
  standalone?: boolean;
  title?: string;
  defaultStructure?: string | number;
  criterias: Array<IConfigurationCriteria>;
}
