<?php
/**
 * Error codes used to checking manage document's filters
 * @class ErrorCodeFLT
 * @brief List all error code for document's filters
 * @see ErrorCode
 */
class ErrorCodeFLT
{
    /**
     * @errorCode the SearchDoc object is not configured with a valid family (fromid property)
     */
    const FLT0001 = "Family of SearchDoc not found.";
    /**
     * @errorCode the attribute was not found on the family
     */
    const FLT0002 = "Attribute '%s' not found on family '%s'.";
    /**
     * @errorCode the attribute's type is not compatible with this filter
     */
    const FLT0003 = "Attribute '%s' with type '%s' from family '%s' is not compatible.";
    /**
     * @errorCode the value is not a scalar nor an array
     */
    const FLT0004 = "Value must be a scalar or an array.";
    /**
     * @errorCode the given value is an array but the attribute is not multiple
     */
    const FLT0005 = "Type mismatch: value is an array but attribute '%s' is not multiple.";
    /**
     * @errorCode the operation cannnot be performed with array values
     */
    const FLT0006 = "Field \"%s\" Value must be scalar.";
    /**
     * @errorCode the attribute must be multiple
     */
    const FLT0007 = "Field '%s' must be multiple.";
    /**
     * @errorCode the attribut must not be multiple
     */
    const FLT0008 = "Field '%s' must not be multiple.";
    /**
     * @errorCode
     */
    const FLT0009 = "Value must be array.";
    /**
     * @errorCode when use docrel and usertag table join by exemple
     * @see \Anakeen\Search\Filters\OrOperator
     */
    const FLT0010 = "The OR operator cannot be used with several filters which use different join ('%s' - '%s')";
    /**
     * @errorCode when use docrel join and isgreater by exemple
     * @see \Anakeen\Search\Filters\OrOperator
     */
    const FLT0011 = "The OR operator cannot be used with several filters which use with join and no join conditions ('%s' - '%s')";
    /**
     * @errorCode when use docrel join and isgreater by exemple
     * @see \Anakeen\Search\SearchCriteria\SearchCriteriaUtils
     */
    const FLT0012 = "The Search Criteria raw data must be an array";
    /**
     * @errorCode when use docrel join and isgreater by exemple
     * @see \Anakeen\Search\SearchCriteria\SearchCriteriaUtils
     */
    const FLT0013 = "The Search Criteria kind value '%s' must be of 'field', 'property', 'virtual' or 'fulltext'";
    /**
     * @errorCode when use docrel join and isgreater by exemple
     * @see \Anakeen\Search\SearchCriteria\SearchCriteriaUtils
     */
    const FLT0014 = "The Search Criteria logic value '%s' must be of 'and' or 'or'";
    /**
     * @errorCode when use docrel join and isgreater by exemple
     * @see \Anakeen\Search\SearchCriteria\SearchCriteriaUtils
     */
    const FLT0015 = "The Search Criteria filter value of kind '%s' must specify a 'field' property";
    /**
     * @errorCode when use docrel join and isgreater by exemple
     * @see \Anakeen\Search\SearchCriteria\SearchCriteriaUtils
     */
    const FLT0017 = "The Search Criteria filter value of kind '%s' must specify an operator value";
    /**
     * @errorCode when use docrel join and isgreater by exemple
     * @see \Anakeen\Search\SearchCriteria\SearchCriteriaUtils
     */
    const FLT0018 = "The operator '%s' is not supported for type '%s' with field multiplicity to '%b' and filter multiplicity to '%b'";
    /**
     * @errorCode when use docrel join and isgreater by exemple
     * @see \Anakeen\Search\SearchCriteria\SearchCriteriaUtils
     */
    const FLT0019 = "The Search Criteria filter does not support multiple filter values yet";
    /**
     * @errorCode the attribut must not be multiple
     */
    const FLT0020 = "Value must be an array of two values";
    /**
     * @errorCode the attribut must not be multiple
     */
    const FLT0021 = "The operator value must be an array with a 'key' value and a 'multipleFilter' flag";
    /**
     * @errorCode value is mandatory
     */
    const FLT0022 = "The value for \"%s\" must not be empty";
}
