<?php

use Anakeen\Core\Internal\FormatCollection;

/**
 * Error codes used to format document list
 * @class ErrorCodeFMTC
 * @brief List all error code for format document list
 * @see   FormatCollection
 * @see   ErrorCode
 */
class ErrorCodeFMTC
{
    /**
     * @errorCode the property used by formatProperties must a property like "title".
     * @see       \Anakeen\Core\Internal\FormatCollection::addProperty()
     */
    const FMTC0001 = 'The document property "%s" is not know. Cannot format document list';
    /**
     * @errorCode array, tab and frame type attributes cannot be formated
     * @see       \Anakeen\Core\Internal\FormatCollection::addAttribute()
     */
    const FMTC0002 = 'Structured attribute "%s" cannot be formated.';
    /**
     * @errorCode the argument must be one of DateAttributeValue::defaultStyle,DateAttributeValue::frenchStyle,DateAttributeValue::isoWTStyle,DateAttributeValue::isoStyle
     * @see       \Anakeen\Core\Internal\FormatCollection::setDateStyle()
     * @see       DateAttributeValue
     */
    const FMTC0003 = 'Date style %s is not correct.';
    
}

