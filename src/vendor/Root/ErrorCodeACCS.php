<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Errors code used to checking application access
 * @class ErrorCodeACCS
 * @see ErrorCode
 * @brief List all error code for application access
 * It is triggered by 'ACCESS' keyword
 * @see ErrorCode
 */
class ErrorCodeACCS
{
    /**
     * @errorCode
     * the application name must be register before define access
     */
    const ACCS0001 = 'application %s is not found, cannot apply access';
    /**
     * @errorCode
     * acl must be referenced in application
     */
    const ACCS0002 = 'acl %s not define for %s application, cannot apply acces';
    /**
     * @errorCode
     * user must be created before apply access to him
     */
    const ACCS0003 = 'user %s not found, cannot apply access';
    /**
     * @errorCode
     * acl syntax is [alphanum|_]{1,63} - and _ characters are granted
     */
    const ACCS0004 = 'acl syntax error, "%s" must be an alphanum limit to 63, cannot apply access';
    /**
     * @errorCode
     * application name syntax is [alphanum|_]{1,63}
     */
    const ACCS0005 = 'appname syntax error, "%s" must be an alphanum limit to 63, cannot apply access';
    /**
     * @errorCode
     * application name is required
     */
    const ACCS0006 = 'appname not set, cannot apply access';
    /**
     * @errorCode
     * user/group name is required
     */
    const ACCS0007 = 'user not set, cannot apply access';
}
