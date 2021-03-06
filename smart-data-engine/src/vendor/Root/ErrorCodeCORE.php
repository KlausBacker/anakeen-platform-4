<?php
/*
 * @author Anakeen
 * @package FDL
*/
namespace {

    use Anakeen\Core\ContextManager;

    /**
     * global Error Code
     * @class ErrorCodeCORE
     * @brief List all error code for user/group/tole mamnagement
     * @see ErrorCode
     */
    class ErrorCodeCORE
    {
        /**
         * @errorCode \Anakeen\Core\Internal\Action::exitError is called
         */
        const CORE0001 = '%s';
        /**
         * @errorCode Api Usage error
         */
        const CORE0002 = '%s';
        /**
         * @errorCode Api Usage help
         */
        const CORE0003 = '%s';
        /**
         * @errorCode application name is not declared
         */
        const CORE0004 = 'Fail to find application %s';
        /**
         * @errorCode action name name is not declared for application
         */
        const CORE0005 = 'Action "%s"  not declared for application "%s" (#%d)';
        /**
         * @errorCode action name name is not declared for application
         */
        const CORE0006 = "Access deny to action \"%s\" [%s].\n Need \"%s\" Acl for \"%s\" user";
        /**
         * @errorCode application name is not available (property available is N)
         */
        const CORE0007 = 'Unavailable application %s';
        /**
         * @errorCode action name is not available for application (property available is N)
         */
        const CORE0008 = 'Action "%s"  not available for application "%s"';
        /**
         * @errorCode action from an admin application is launched from non admin mode
         */
        const CORE0009 = 'Action "%s" [%s]  from application "%s" [%s] must be run in admin mode';
        /**
         * @errorCode Guest acess is not allowed (see CORE_ALLOW_GUEST)
         */
        const CORE0010 = 'Guest access not allowed';
        /**
         * @errorCode The locale is not supported by the operating system
         */
        const CORE0011 = "Locale '%s' is not supported by the operating system";
        /**
         * @errorCode Access forbidden to action
         */
        const CORE0012 = "Access deny : %s";
        /**
         * @errorCode Trying to access a non-existing user account
         */
        const CORE0013 = "Error : User [%s] doesn't exists";
        /**
         * @errorCode Trying to access a desactivated user account
         */
        const CORE0014 = "Error : User account [%s] is desactivated";
        /**
         * @errorCode The .app name is not found
         */
        const CORE0015 = 'Fail to find application file : %s';
        /**
         * @errorCode The dbaccess.php cannot be included
         */
        const CORE0016 = 'Cannot load dbaccess file : %s';
        /**
         * @errorCode DMust call initContext before
         * @see \Anakeen\Core\ContextManager::initContext()
         */
        const CORE0017 = "Context not initialized yet";

        /**
         * @errorCode Router File config must be a valid XML
         */
        const CORE0019 = "Cannot decode XML of router config file \"%s\"";
        /**
         * @errorCode Router Config directory must exists
         */
        const CORE0020 = "Cannot read router config directory  \"%s\"";
        /**
         * @errorCode Router Config methods limited to get, put, post, delete
         */
        const CORE0021 = "Router : method \"%s\" not supported";
        /**
         * @errorCode The user is not authenticated
         */
        const CORE0022 = "User not authenticated";
        /**
         * @errorCode Generate document class syntex error
         */
        const CORE0023 = 'Error generating file document class file "%s" : %s';
        /**
         * @errorCode Generate document class syntex error
         */
        const CORE0024 = 'Error generating file attributes document class file "%s" : %s';
        /**
         * @errorCode Try to register another pat for config files
         */
        const CORE0025 = 'Cannot register config directory "%s" : this directory not exists';
        /**
         * @errorCode Try to get current user
         * @see ContextManager::getCurrentUser()
         */
        const CORE0026 = 'User not authenticated';
        /**
         * @errorCode When get user parameter
         * @see \Anakeen\Core\Internal\ContextParameterManager::getUserValue
         */
        const CORE0100 = 'User parameter "%s" not exists';
        /**
         * @errorCode When get user parameter
         * @see \Anakeen\Core\Internal\ContextParameterManager::setValue
         */
        const CORE0101 = 'Cannot modify context parameter "%s" : %s';
        /**
         * @errorCode When get user parameter
         * @see \Anakeen\Core\Internal\ContextParameterManager::setValue
         */
        const CORE0102 = 'Unknow context parameter "%s"';
        /**
         * @errorCode When get user parameter
         * @see \Anakeen\Core\Internal\ContextParameterManager::setValue
         */
        const CORE0103 = 'Unknow context user parameter "%s"';
        /**
         * @errorCode When get user parameter
         * @see \Anakeen\Core\Internal\ContextParameterManager::getUserValue
         */
        const CORE0104 = 'Context parameter "%s" is not a user parameter';
        /**
         * @errorCode The array parameters to the addArrayRow method need to contain strings
         * @see \Anakeen\Core\Internal::addArrayRow
         */
        const CORE0105 = 'The array parameter in the "addArrayRow" method contain an "%s" type';
        /**
         * @errorCode The third level to do the addArrayRow need to be an string
         * @see \Anakeen\Core\Internal::addArrayRow
         */
        const CORE0106 = 'The third level in the array parameter in the "addArrayRow" method need to be an string not an "%s"';
        /**
         * @errorCode The argument is scalar, but you need to send an array
         * @see \Anakeen\Core\Internal::addArrayRow
         */
        const CORE0107 = 'Values "%s" must be an array';
        /**
         * @errorCode The smart field is not part of array (bad format array)
         * @see \Anakeen\Core\Internal::addArrayRow
         */
        const CORE0108 = "Smart field \"%s\" is not a part of array \"%s\"";
        /**
        * @errorCode The attribute is not part of array (bad format array)
        * @see \Anakeen\Core\Internal::addArrayRow
        */
        const CORE0109 = "Index must reference Smart Field identifier in array \"%s\"";
    }
}
