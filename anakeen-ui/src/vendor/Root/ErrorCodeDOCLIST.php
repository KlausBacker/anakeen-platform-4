<?php

/**
 * Errors code used
 * @class ErrorCodeDocList
 * @brief List all error code errors
 * @see   ErrorCodeDocList
 */
class ErrorCodeDOCLIST
{
    /**
     * @errorCode in case of family not found
     */
    const DOCLIST0001 = 'Error, family "%s" not found';

    /**
     * @errorCode in case of collection not found
     */
    const DOCLIST0002 = 'Error, collection "%s" not found';

    /**
     * @errorCode in case of element is not family or collection
     */
    const DOCLIST0003 = 'Error, the element "%s" is not a family or a collection';
}

