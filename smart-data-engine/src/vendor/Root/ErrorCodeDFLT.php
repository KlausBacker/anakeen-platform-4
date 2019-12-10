<?php
/*
 * @author Anakeen
 * @package FDL
*/

/**
 * Errors code used to checking METHOD keyword
 * @class ErrorCodeDFLT
 * @brief List all error code for METHOD
 * @see ErrorCode
 */
class ErrorCodeDFLT
{
    /**
     * @errorCode default smart field must reference smart field (63 max alphanum characters)
     */
    const DFLT0001 = 'default smart field "%s" syntax error in "%s" smart structure';
    /**
     * @errorCode default smart field must reference smart field
     */
    const DFLT0002 = 'default smart field reference is empty in "%s" smart structure';
    /**
     * @errorCode DEFAULT method is not correctly defined
     */
    const DFLT0003 = 'the default "%s" reference method "%s" in "%s" smart structure : %s';
    /**
     * @errorCode error definition of method in DEFAULT key
     * @see ErrorCodeATTR::ATTR1260
     * @see ErrorCodeATTR::ATTR1263
     * @see ErrorCodeATTR::ATTR1261
     */
    const DFLT0004 = 'method error smart field "%s" in smart structure "%s" : %s';
    /**
     * @errorCode unknow smart field found in DEFAULT key
     */
    const DFLT0005 = 'the default smart field reference "%s" is not found in "%s" smart structure';
    /**
     * @errorCode for array default values must be json valide encoded
     */
    const DFLT0006 = 'the default array smart field reference "%s" is not json encoded "%s" for "%s" smart structure';
    /**
     * @errorCode for array default values must be json valide encoded or method call
     */
    const DFLT0007 = 'the default array smart field reference "%s" is not json encoded or method no return a valid array "%s" for "%s" smart structure';
    /**
     * @errorCode when use default for array smart field the value must be an array of array
     */
    const DFLT0008 = 'the default array smart field reference "%s" not return a valid array ( "%s" return "%s") for "%s" smart structure';
    /**
     * @errorCode when use default fot array smart field the value must be an array of array. Somes returns row are invalid
     */
    const DFLT0009 = 'the default array smart field reference "%s" not return a valid array ( "%s" return "%s") for "%s" smart structure : "%s"';
    /**
     * @errorCode when use default fot array smart field the value must be an array of array. Somes returns row are invalid
     */
    const DFLT0010 = 'the reference "%s" is a parameter not a smart field for smart structure : "%s"';
}
