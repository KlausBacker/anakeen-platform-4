import IConfigurationCriteria from "./IConfigurationCriteria";

export default interface ISmartCriteriaConfiguration {
  title: string;
  defaultStructure: string | number;
  criterias: Array<IConfigurationCriteria>;
}
