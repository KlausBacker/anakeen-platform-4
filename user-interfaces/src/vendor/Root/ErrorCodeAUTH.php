<?php

/**
 * Errors code used
 * @class ErrorCodeAuth
 * @see   ErrorCodeAuth
 * @brief List all error code errors
 * @see   ErrorCodeAuth
 */
class ErrorCodeAUTH
{
    /**
     * @errorCode in case of POST
     */
    const AUTH0001 = 'Error, access forbidden';

    /**
     * @errorCode Session create in case of expired date of deactive account
     */
    const AUTH0002 = 'Account access not valid (%s)';
    /**
     * @errorCode when mail template for ask password not found
     */
    const AUTH0010 = 'Cannot find mail template "%s"';
    /**
     * @errorCode
     */
    const AUTH0011 = 'Several user use same email address "%s". You must use your login identifier';
    /**
     * @errorCode when send ask mail
     */
    const AUTH0012 = 'Cannot send email "%s"';
    /**
     * @errorCode user with email or login
     */
    const AUTH0013 = 'Cannot find user "%s"';
    /**
     * @errorCode when try to recover password - need user to has email
     */
    const AUTH0014 = 'User "%s" has no mail address';
    /**
     * @errorCode mistmatch between login and token
     */
    const AUTH0020 = 'Invalid access.';
    /**
     * @errorCode Error when change password
     */
    const AUTH0021 = 'Password not changed : %s';
    /**
     * @errorCode Error password not strong enough
     */
    const AUTH0022 = 'Password not changed : %s';
    /**
     * @errorCode Error when username and or password is incorrect
     */
    const AUTH0023 = 'Username and/or password is incorrect';
}
