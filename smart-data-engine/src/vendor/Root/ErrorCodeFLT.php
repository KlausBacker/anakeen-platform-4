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
    const FLT0006 = "Value must be scalar.";
    /**
     * @errorCode the attribute must be multiple
     */
    const FLT0007 = "Attribute '%s' must be multiple.";
    /**
     * @errorCode the attribut must not be multiple
     */
    const FLT0008 = "Attribute '%s' must not be multiple.";
    /**
     * @errorCode
     */
    const FLT0009 = "Value must be array.";
}
