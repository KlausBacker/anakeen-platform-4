<?php

class ErrorCodeRouter
{

    /**
     * @errorCode Route  must be defined when set token
     * @see \Anakeen\Router\AuthenticatorManager::getAuthorizationToken
     */
    const ROUTER0101 = 'No route pattern given #%d';
    /**
     * @errorCode Route must contain at leat 2 characters
     * @see \Dcp\HttpApi\V1\AuthenticatorManager::getAuthorizationToken
     */
    const ROUTER0102 = 'Invalid route pattern given : "%s"';
    /**
     * @errorCode Route method must be set
     * @see \Dcp\HttpApi\V1\AuthenticatorManager::getAuthorizationToken
     */
    const ROUTER0103 = 'Invalid route methods';
    /**
     * @errorCode Token cannot be recorded in database
     * @see \Anakeen\Router\AuthenticatorManager::getAuthorizationToken
     */
    const ROUTER0104 = 'Create token fail: "%s"';
    /**
     * @errorCode Token must affect route
     * @see \Anakeen\Router\AuthenticatorManager::getAuthorizationToken
     */
    const ROUTER0105 = 'Empty route is not allowed';
    /**
     * @errorCode Token must affect route
     * @see \Anakeen\Router\AuthenticatorManager::getAuthorizationToken
     */
    const ROUTER0106 = 'User is not valid';

}