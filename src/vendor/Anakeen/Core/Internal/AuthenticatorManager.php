<?php
/**
 * Authenticator manager class
 *
 * Manage authentification method (classes)
 *
 */

namespace Anakeen\Core\Internal;

use Anakeen\Core\DocManager;
use Dcp\Core\Exception;

include_once("FDL/LegacyDocManager.php");

class AuthenticatorManager
{
    /**
     * @var \Session
     */
    public static $session = null;
    const AccessBug = -1;
    const AccessOk = 0;
    const AccessHasNoLocalAccount = 1;
    const AccessMaxLoginFailure = 2;
    const AccessAccountIsNotActive = 3;
    const AccessAccountHasExpired = 4;
    const AccessNotAuthorized = 5;
    const NeedAsk = 6;
    /**
     * @var Authenticator|HtmlAuthenticator|OpenAuthenticator
     */
    public static $auth = null;
    public static $provider_errno = 0;

    public static function checkAccess($authtype = null, $noask = false)
    {
        /*
         * Part 1: check authentication
        */
        $status = self::checkAuthentication($authtype, $noask);
        if ($status === \Anakeen\Core\Internal\Authenticator::AUTH_NOK) {
            $error = 1;
            $providerErrno = self::$auth->getProviderErrno();
            if ($providerErrno != 0) {
                self::$provider_errno = $providerErrno;
                switch ($providerErrno) {
                    case \Anakeen\Core\Internal\AuthentProvider::ERRNO_BUG_639:
                        // User must change his password
                        $error = self::AccessBug;
                        break;
                }
            }
            $remote_addr = isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : "";
            $auth_user = isset($_REQUEST["auth_user"]) ? $_REQUEST["auth_user"] : "";
            $http_user_agent = isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : "";
            self::secureLog("failure", "invalid credential",
                self::$auth->provider->parms['type'] . "/" . self::$auth->provider->parms['AuthentProvider'], $remote_addr,
                $auth_user, $http_user_agent);
            // count login failure
            if (\Anakeen\Core\ContextManager::getApplicationParam("AUTHENT_FAILURECOUNT") > 0) {
                $wu = new \Anakeen\Core\Account();
                if ($wu->SetLoginName(self::$auth->getAuthUser())) {
                    if ($wu->id != 1) {
                        /**
                         * @var \SmartStructure\IUSER $du
                         */
                        $du = DocManager::getDocument($wu->fid);
                        if ($du && $du->isAlive()) {
                            $du->disableEditControl();
                            $du->increaseLoginFailure();
                            $du->enableEditControl();
                        }
                    }
                }
            }
            self::clearGDocs();
            return $error;
        }
        // Authentication success
        /*
         * Part 2: check authorization
        */
        $ret = self::checkAuthorization();
        if ($ret !== self::AccessOk) {
            return $ret;
        }

        $login = static::$auth->getAuthUser();
        /*
         * All authenticators are not necessarily based on sessions (i.e. 'basic')
        */
        if (method_exists(self::$auth, 'getAuthSession')) {
            self::$session = self::$auth->getAuthSession();
            /**
             * @var self::$session Session
             */
            if (self::$session->read('username') == "") {
                self::secureLog(
                    "failure",
                    "username should exists in session",
                    $authprovider = "",
                    $_SERVER["REMOTE_ADDR"],
                    $login,
                    $_SERVER["HTTP_USER_AGENT"]
                );
                throw new \Dcp\Exception("Authent Session Error");
            }
        }

        self::clearGDocs();
        return self::AccessOk;
    }

    protected static function checkAuthentication($authtype = null, $noask = false)
    {
        self::$provider_errno = 0;

        self::$auth = static::getAuthenticatorClass();
        $authProviderList = static::getAuthProviderList();
        $status = false;
        foreach ($authProviderList as $authProvider) {
            self::$auth = static::getAuthenticatorClass($authtype, $authProvider);
            $status = self::$auth->checkAuthentication();
            if ($status === \Anakeen\Core\Internal\Authenticator::AUTH_ASK) {
                if ($noask) {
                    return self::NeedAsk;
                } else {
                    self::$auth->askAuthentication(array());
                    exit(0);
                }
            }
            if ($status === \Anakeen\Core\Internal\Authenticator::AUTH_OK) {
                break;
            }
        }
        return $status;
    }

    protected static function getAuthenticatorClass($authtype = null, $provider = \Anakeen\Core\Internal\Authenticator::nullProvider)
    {
        if (!$authtype) {
            $authtype = static::getAuthType();
        }
        if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $authtype)) {
            throw new \Dcp\Exception(sprintf("Invalid authtype '%s'", $authtype));
        }

        $authClass = ucfirst(strtolower($authtype)) . "Authenticator";
        if (!\Anakeen\Core\Internal\Autoloader::classExists($authClass)) {
            throw new \Dcp\Exception(sprintf("Cannot find authenticator '%s'", $authtype));
        }
        return new $authClass($authtype, $provider);
    }

    /**
     * @return string
     */
    public static function getAuthProvider()
    {
        $authprovider = getDbAccessValue('authentProvider');

        if ($authprovider == "") {
            $authprovider = "internal";
        }

        return trim($authprovider);
    }

    /**
     * @return array
     */
    public static function getAuthProviderList()
    {
        return preg_split("/\\s*,\\s*/", static::getAuthProvider());
    }

    public static function getAuthType()
    {
        if (array_key_exists('authtype', $_GET)) {
            if ($_GET['authtype'] === "apache") {
                throw new \Dcp\Exception(sprintf("apache authtype not allowed.\n"));
            }
            return $_GET['authtype'];
        }
        if (!empty($_GET[\Anakeen\Core\Internal\OpenAuthenticator::openGetId])) {
            return "open";
        }

        $scheme = self::getAuthorizationScheme();
        if ($scheme) {
            switch ($scheme) {
                case \Anakeen\Router\TokenAuthenticator::AUTHORIZATION_SCHEME:
                    return "token";
                case \Anakeen\Core\Internal\OpenAuthenticator::openAuthorizationScheme:
                    return "open";
                case \Anakeen\Core\Internal\BasicAuthenticator::basicAuthorizationScheme:
                    return "basic";
                default:
                    throw new Exception(sprintf("Invalid authorization method \"%s\"", $scheme));
            }
        }

        $authMode = getDbAccessValue('authentMode');

        if ($authMode == "") {
            $authMode = "html";
        }

        return trim($authMode);
    }

    public static function getAuthTypeParams()
    {
        $authModeConfig = getDbAccessValue('authentModeConfig');
        if (!is_array($authModeConfig)) {
            throw new \Dcp\Exception('FILE0006');
        }

        if (!array_key_exists(self::getAuthType(), $authModeConfig)) {
            return array();
        }

        return $authModeConfig[static::getAuthType()];
    }

    public static function getAuthorizationValue()
    {
        if (php_sapi_name() !== 'cli') {
            $headers = apache_request_headers();

            foreach ($headers as $k => $v) {
                if (strtolower($k) === "authorization") {
                    if (preg_match("/^([a-z0-9]+)\\s+(.*)$/i", $v, $reg)) {
                        return ["scheme" => trim($reg[1]), "token" => trim($reg[2])];
                    }
                }
            }
        }
        return false;
    }

    protected static function getAuthorizationScheme()
    {
        $authValue = self::getAuthorizationValue();
        if ($authValue) {
            return $authValue["scheme"];
        }
        return "";
    }

    public static function closeAccess()
    {
        self::$auth = static::getAuthenticatorClass();

        if (method_exists(self::$auth, 'logout')) {
            if (is_object(self::$auth->provider)) {
                self::secureLog("close", "see you tomorrow",
                    self::$auth->provider->parms['type'] . "/" . self::$auth->provider->parms['AuthentProvider'],
                    $_SERVER["REMOTE_ADDR"], self::$auth->getAuthUser(), $_SERVER["HTTP_USER_AGENT"]);
            } else {
                self::secureLog("close", "see you tomorrow");
            }
            self::$auth->logout(null);
            return;
        }

        throw new \Dcp\Exception(sprintf("logout method not supported by authtype '%s'", static::getAuthType()));
    }

    /**
     * Send a 401 Unauthorized HTTP header
     */
    public function authenticate(&$action)
    {
        header('WWW-Authenticate: Basic realm="' . \Anakeen\Core\ContextManager::getApplicationParam(
                "CORE_REALM",
                "Anakeen Platform connection"
            ) . '"');
        header('HTTP/1.0 401 Unauthorized');
        echo _("Vous devez entrer un nom d'utilisateur valide et un mot de passe correct pour acceder a cette ressource");
        exit;
    }

    public static function secureLog(
        $status = "",
        $additionalMessage = "",
        $provider = "",
        $clientIp = "",
        $account = "",
        $userAgent = ""
    ) {
        global $_GET;
        $log = new \Anakeen\Core\Internal\Log("", "Session", "Authentication");
        $facility = constant(\Anakeen\Core\ContextManager::getApplicationParam("AUTHENT_LOGFACILITY", "LOG_AUTH"));
        $log->wlog("S", sprintf(
            "[%s] [%s] [%s] [%s] [%s] [%s]",
            $status,
            $additionalMessage,
            $provider,
            $clientIp,
            $account,
            $userAgent
        ), null, $facility);
        return 0;
    }

    public static function clearGDocs()
    {
        \Anakeen\Core\DocManager::cache()->clear();
    }

    public static function getAccount()
    {
        $login = self::$auth->getAuthUser();
        $account = new \Anakeen\Core\Account();
        if ($account->setLoginName($login)) {
            return $account;
        }
        return false;
    }

    /**
     * Get Provider's protocol version.
     *
     * @param \Anakeen\Core\Internal\AuthentProvider $provider
     *
     * @return int version (0, 1, etc.)
     */
    public static function _getProviderProtocolVersion(\Anakeen\Core\Internal\AuthentProvider $provider)
    {
        if (!isset($provider->PROTOCOL_VERSION)) {
            return 0;
        }
        return $provider->PROTOCOL_VERSION;
    }

    /**
     * Main authorization check entry point
     *
     * @return int
     * @throws \Dcp\Exception
     */
    protected static function checkAuthorization()
    {
        $login = static::$auth->getAuthUser();
        $wu = new \Anakeen\Core\Account();
        $existu = false;
        if ($wu->SetLoginName($login)) {
            $existu = true;
        }

        if (!$existu) {
            static::secureLog("failure", "login have no Dynacase account",
                static::$auth->provider->parms['type'] . "/"
                . static::$auth->provider->parms['AuthentProvider'], $_SERVER["REMOTE_ADDR"], $login,
                $_SERVER["HTTP_USER_AGENT"]);
            return static::AccessHasNoLocalAccount;
        }

        $protoVersion = self::_getProviderProtocolVersion(self::$auth->provider);
        if (!is_integer($protoVersion)) {
            throw new \Dcp\Exception(sprintf("Invalid provider protocol version '%s' for provider '%s'.", $protoVersion,
                get_class(self::$auth->provider)));
        }

        switch ($protoVersion) {
            case 0:
                return self::protocol_0_authorization(array(
                    'username' => $login,
                    'dcp_account' => $wu
                ));
                break;
        }
        throw new \Dcp\Exception(sprintf("Unsupported provider protocol version '%s' for provider '%s'.", $protoVersion,
            get_class(self::$auth->provider)));
    }

    /**
     * Protocol 0: check only Dynacase's authorization.
     *
     * @param array $opt
     *
     * @return int
     */
    private static function protocol_0_authorization($opt)
    {
        $authz = self::checkProviderAuthorization($opt);
        if ($authz !== self::AccessOk) {
            return $authz;
        }
        return self::checkDynacaseAuthorization($opt);
    }

    /**
     * Check Provider's authorization.
     *
     * @param array $opt
     *
     * @return int
     */
    private static function checkProviderAuthorization($opt)
    {
        $authz = self::$auth->checkAuthorization($opt);
        if ($authz === true) {
            return self::AccessOk;
        }
        return self::AccessNotAuthorized;
    }

    /**
     * Check Dynacase's authorization.
     *
     * @param array $opt
     *
     * @throws \Dcp\Exception
     * @return int
     */
    private static function checkDynacaseAuthorization($opt)
    {
        $login = $opt['username'];
        $wu = $opt['dcp_account'];
        if ($wu->id != 1) {
            include_once("FDL/LegacyDocManager.php");
            /**
             * @var \SmartStructure\IUSER $du
             */
            $du = new_Doc(getDbAccess(), $wu->fid);
            // First check if account is active
            if (!$du->isAccountActive()) {
                static::secureLog("failure", "inactive account",
                    static::$auth->provider->parms['type'] . "/"
                    . static::$auth->provider->parms['AuthentProvider'], $_SERVER["REMOTE_ADDR"], $login,
                    $_SERVER["HTTP_USER_AGENT"]);
                static::clearGDocs();
                return static::AccessAccountIsNotActive;
            }
            // check if the account expiration date is elapsed
            if ($du->accountHasExpired()) {
                static::secureLog("failure", "account has expired",
                    static::$auth->provider->parms['type'] . "/"
                    . static::$auth->provider->parms['AuthentProvider'], $_SERVER["REMOTE_ADDR"], $login,
                    $_SERVER["HTTP_USER_AGENT"]);
                static::clearGDocs();
                return static::AccessAccountHasExpired;
            }
            // check count of login failure
            $maxfail = \Anakeen\Core\ContextManager::getApplicationParam("AUTHENT_FAILURECOUNT");
            if ($maxfail > 0 && $du->getRawValue("us_loginfailure", 0) >= $maxfail) {
                static::secureLog("failure", "max connection (" . $maxfail . ") attempts exceeded",
                    static::$auth->provider->parms['type'] . "/"
                    . static::$auth->provider->parms['AuthentProvider'], $_SERVER["REMOTE_ADDR"], $login,
                    $_SERVER["HTTP_USER_AGENT"]);
                static::clearGDocs();
                return static::AccessMaxLoginFailure;
            }
            // authen OK, max login failure OK => reset count of login failure
            $du->disableEditControl();
            $du->resetLoginFailure();
            $du->enableEditControl();
        }

        return static::AccessOk;
    }
}
