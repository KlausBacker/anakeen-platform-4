<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Authenticator class
 *
 * Top-level class to authenticate and authorize users
 *
 * @author Anakeen
 * @version $Id: Class.Authenticator.php,v 1.6 2009/01/16 13:33:00 jerome Exp $
 * @package FDL
 * @subpackage
 */
/**
 */
namespace Anakeen\Core\Internal;

abstract class Authenticator
{
    /* Authentication success */
    const AUTH_OK = 0;
    /* Authentication failed */
    const AUTH_NOK = 1;
    /* Authentication status cannot be determined, and credentials should be asked */
    const AUTH_ASK = 2;
    
    const nullProvider = "__for_logout__";
    /**
     * @var \Anakeen\Core\Internal\AuthentProvider
     */
    public $provider = null;
    public $parms;

    public function __construct($authtype, $authprovider)
    {
        
        if ($authtype == "") {
            throw new \Dcp\Exception(__METHOD__ . " " . "Error: authentication mode not set");
        }
        if ($authprovider == "") {
            throw new \Dcp\Exception(__METHOD__ . " " . "Error: authentication provider not set");
        }
        
        $tx = array(
            'type' => $authtype,
            'AuthentProvider' => $authprovider
        );
        $ta = \Anakeen\Router\AuthenticatorManager::getAuthTypeParams();
        if ($authprovider != self::nullProvider) {
            $tp = self::getAuthParam($authprovider);
            $this->parms = array_merge($tx, $ta, $tp);
            
            if (!array_key_exists('AuthentProvider', $this->parms)) {
                throw new \Dcp\Exception(__METHOD__ . " " . "Error: provider parm not specified at __construct");
            }

            if (empty($tp["class"])) {
                throw new \Dcp\Exception(__METHOD__ . " " . "Error: \"class\" parameter must be defined in provider config not found");
            }
            $providerClass = $tp["class"];

            //     error_log("Using authentication provider [".$providerClass."]");
            $this->provider = new $providerClass($authprovider, $this->parms);
            if (!is_a($this->provider, \Anakeen\Core\Internal\AuthentProvider::class)) {
                throw new \Dcp\Exception(__METHOD__ . " " . sprintf("Error: provider with class '%s' does not inherits from class 'Provider'.", $providerClass));
            }
        } else {
            $this->parms = array_merge($tx, $ta);
        }
    }
    public static function getAuthParam($provider = "")
    {
        if ($provider == "") {
            return array();
        }
        $authentConfigs = GlobalParametersManager::getDbAccessValue('authentProvidersConfig');
        if (!is_array($authentConfigs)) {
            return array();
        }
        
        if (!array_key_exists($provider, $authentConfigs)) {
            error_log(__FUNCTION__ . ":" . __LINE__ . "provider " . $provider . " does not exists in authentProvidersConfig");
            return array();
        }
        
        return $authentConfigs[$provider];
    }
    

    
    public static function documentUserExists($username)
    {
        
        $u = new \Anakeen\Core\Account();
        if ($u->SetLoginName($username)) {
            $du = \Anakeen\Core\DocManager::getDocument($u->fid);
            if ($du && $du->isAlive()) {
                return true;
            }
        }
        return false;
    }
    
    public function tryInitializeUser($username)
    {
        if (!$this->provider->canICreateUser()) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . sprintf("Authentication failed for user '%s' because auto-creation is disabled for provider '%s'!", $username, $this->provider->pname));
            return false;
        }
        $err = $this->provider->initializeUser($username);
        if ($err != "") {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . sprintf("Error creating user '%s' err=[%s]", $username, $err));
            return false;
        }
        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . sprintf("Initialized user '%s'!", $username));
        return true;
    }
    
    public function getProviderErrno()
    {
        if ($this->provider) {
            return $this->provider->errno;
        }
        return 0;
    }
    
    public function getAuthApp()
    {
        if (isset($this->parms['auth']['app'])) {
            return $this->parms['auth']['app'];
        }
        return false;
    }
    
    abstract public function checkAuthentication();
    abstract public function checkAuthorization($opt);
    abstract public function askAuthentication($args);
    abstract public function getAuthUser();
    abstract public function getAuthPw();
    abstract public function logout($redir_uri = '');
    abstract public function setSessionVar($name, $value);
    abstract public function getSessionVar($name);
}
