<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Errors code used to checking WIDID keyword
 * @class ErrorCodeRESE
 * @brief List all error code for WIDID
 * @see ErrorCode
 */
class ErrorCodeWID
{
    /**
     * @errorCode
     *  WID reference must be reference existing workflow
     */
    const WID0001 = 'WID "%s" workflow is not found in family "%s"';
    /**
     * @errorCode
     *  WID reference must be a workflow document
     */
    const WID0002 = 'WID "%s" is not a workflow in family "%s"';
    /**
     * @errorCode
     *  error when try retrieve WID reference
     */
    const WID0003 = 'WID reference error : "%s" for family "%s"';
}
