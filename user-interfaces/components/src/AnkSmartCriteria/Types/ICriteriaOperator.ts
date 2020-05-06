export interface ICriteriaOperator {
  filterMultiple: boolean;
  options: CriteriaOption[];
  additionalOptions: CriteriaAdditionalOptions[];
  key: CriteriaOperator;
}

export enum CriteriaOption {
  NOT = "not",
  START_WITH = "startWith",
  ALL = "all",
  EQUAL = "equal",
  EQUAL_LEFT = "equalLeft",
  EQUAL_RIGHT = "equalRight"
}

export enum CriteriaAdditionalOptions {
  NO_DIACRITICS = "noDiacritics",
  NO_CASE = "noCase"
}

export enum CriteriaOperator {
  NONE = "none",
  IS_EMPTY = "isEmpty",
  EQUALS = "equals",
  CONTAINS = "contains",
  LESSER = "lesser",
  GREATER = "greater",
  BETWEEN = "between",
  TITLE_CONTAINS = "titleContains",
  EQUALS_ONE = "equalsOne",
  TITLE_EQUALS_ONE = "titleEqualsOne",
  ONE_EMPTY = "oneEmpty",
  ONE_EQUALS = "oneEquals",
  ONE_CONTAINS = "oneContains",
  ONE_LESSER = "oneLesser",
  ONE_GREATER = "oneGreater",
  ONE_BETWEEN = "oneBetween",
  ONE_TITLE_EQUALS = "oneTitleEquals",
  ONE_TITLE_CONTAINS = "oneTitleContains",
  ONE_EQUALS_MULTI = "oneEqualsMulti"
}
