<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Errors code used to checking family's properties' parameters
 * @class ErrorCodePROP
 * @brief List all error code for properties configuration
 * @see ErrorCode
 */
class ErrorCodePROP
{
    /**
     * @errorCode
     * The property's name is missing
     */
    const PROP0100 = 'missing property name';
    /**
     * @errorCode
     * The property's name is malformed (it should conform to the attribute's name syntax)
     */
    const PROP0101 = 'syntax error for property name "%s"';
    /**
     * @errorCode
     * The property's value can't be verified beacause the module's associated with it is not installed.
     */
    const PROP0102 = 'cannot verify validity of property %s. Module %s must be installed.';
    /**
     * @errorCode
     * The property's parameter's value is missing
     */
    const PROP0200 = 'missing parameters values';
    /**
     * @errorCode
     * The property's parameter's value is malformed (it should conform to the syntax "<pName>=<pValue>")
     */
    const PROP0201 = 'malformed parameter value "%s"';
    /**
     * @errorCode
     * The property's parameter's pName has no valid class name.
     * This will occurs when setting an unknown/unsupported parameter's value.
     */
    const PROP0202 = 'unknown class name for parameter "%s"';
}
