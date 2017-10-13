<?php

namespace {
    /**
     * Errors code used
     * @class ErrorCodeAuth
     * @see ErrorCodeAuth
     * @brief List all error code errors
     * @see ErrorCodeAuth
     */
    class ErrorCodeAuth
    {
        /**
         * @errorCode in case of POST
         */
        const AUTH0001 = 'Error, access forbidden';
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
    }
}
