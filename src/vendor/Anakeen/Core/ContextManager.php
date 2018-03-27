<?php

namespace Anakeen\Core;

use Anakeen\Router\AuthenticatorManager;

class ContextManager
{
    /**
     * @var \Application
     */
    protected static $coreApplication = null;
    /**
     * @var \Anakeen\Core\Internal\Action
     */
    protected static $coreAction = null;
    /**
     * @var \Anakeen\Core\Account
     */
    protected static $coreUser = null;

    /**
     * @var \Composer\Autoload\ClassLoader
     */
    protected static $loader;
    protected static $coreParams;
    protected static $language;

    /**
     *
     * @param string $core_lang
     *
     * @return bool|array
     */
    public static function getLocaleConfig($core_lang = '')
    {
        if (empty($core_lang)) {
            $core_lang = self::getApplicationParam("CORE_LANG", "fr_FR");
        }
        $lng = substr($core_lang, 0, 2);
        if (preg_match('#^[a-z0-9_\.-]+$#i', $core_lang)
            && file_exists(DEFAULT_PUBDIR . "/locale/" . $lng . "/lang.php")) {
            include(DEFAULT_PUBDIR . "/locale/" . $lng . "/lang.php");
        } else {
            include(DEFAULT_PUBDIR . "/locale/fr/lang.php");
        }
        if (!isset($lang) || !isset($lang[$core_lang]) || !is_array($lang[$core_lang])) {
            return false;
        }
        return $lang[$core_lang];
    }

    public function getLocales()
    {
        static $locales = null;

        if ($locales === null) {
            $lang = array();
            include('CORE/lang.php');
            $locales = $lang;
        }
        return $locales;
    }

    /**
     * Initialise application context
     *
     * @param \Anakeen\Core\Account    $account
     * @param string        $appName
     * @param string        $actionName
     * @param \Session|null $session
     *
     * @throws \Dcp\Db\Exception
     * @throws \Exception
     */
    public static function initContext(\Anakeen\Core\Account $account, $appName = "CORE", $actionName = "", \Session $session = null)
    {
        global $action;
        set_include_path(self::getRootDirectory() . PATH_SEPARATOR . get_include_path());

        $coreApplication = new \Application();
        $coreApplication->user = &$account;
        self::$coreUser = &$account;
        $coreApplication->Set("CORE", $CoreNull);
        $coreApplication->session = $session;
        if (!$coreApplication->session) {
            $coreApplication->session = new \Session();
        }

        self::_initCoreVolatileParam($coreApplication);
        if ($appName && $appName !== "CORE") {
            $application = new \Application();
            $application->set($appName, $coreApplication);
            self::$coreApplication = $application;
            if (!$actionName) {
                $actionName = self::getRootActionName($application);
            }
        } else {
            self::$coreApplication = $coreApplication;
        }

        self::$coreAction = new \Anakeen\Core\Internal\Action();
        $action = new \Anakeen\Core\Internal\Action();
        self::$coreAction = &$action;
        if ($actionName) {
            self::$coreAction->Set($actionName, self::$coreApplication);
        } else {
            self::$coreAction->parent = self::$coreApplication;
            self::$coreAction->session = &self::$coreApplication->session;
        }
        self::$coreAction->user =& $account;


        self::setLanguage(self::getApplicationParam("CORE_LANG", "fr_FR"));
    }

    protected static function getRootActionName(\Application $application)
    {
        DbManager::query(
            sprintf("select name from action where id_application=%d and root='Y'", $application->id),
            $actionRoot,
            true,
            true
        );
        return $actionRoot;
    }

    public static function recordContext(\Anakeen\Core\Account $account, \Anakeen\Core\Internal\Action $action = null)
    {
        self::$coreUser = &$account;
        if ($action) {
            self::$coreAction = &$action;
            self::$coreApplication = & $action->parent;
        }
    }


    /**
     * Control user has a good session
     * Complete AuthenticatorManager singleton
     *
     * @return \Anakeen\Core\Account
     */
    public static function authentUser()
    {
        if (php_sapi_name() !== 'cli') {
            // Ask authentification if HTML required
            $urlInfo = parse_url($_SERVER["REQUEST_URI"]);
            $headers = apache_request_headers();
            $askAuthent = (preg_match("/\\.html$/", $urlInfo["path"])
                || (!empty($headers["Accept"])
                    && preg_match("@\\btext/html\\b@", $headers["Accept"])));
        } else {
            $askAuthent = false;
        }

        $status = AuthenticatorManager::checkAccess(null, !$askAuthent);

        switch ($status) {
            case \Authenticator::AUTH_OK: // it'good, user is authentified
                break;

            default:
                $auth = AuthenticatorManager::$auth;
                if ($auth === false) {
                    $exception = new \Anakeen\Router\Exception("Could not get authenticator");
                    $exception->setHttpStatus("500", "Could not get authenticator");
                    $exception->setUserMessage("Could not get authenticator");
                    throw $exception;
                }

                $exception = new \Anakeen\Router\Exception("User must be authenticated");
                $exception->setHttpStatus("403", "Forbidden");
                $exception->setUserMessage(___("Access not granted", "ank"));
                throw $exception;
        }
        $_SERVER['PHP_AUTH_USER'] = AuthenticatorManager::$auth->getAuthUser();
        // First control
        if (empty($_SERVER['PHP_AUTH_USER'])) {
            $exception = new \Anakeen\Router\Exception("User must be authenticated");
            $exception->setHttpStatus("403", "Forbidden");
            $exception->setUserMessage(___("Access not granted", "ank"));
            throw $exception;
        }
        $u = new \Anakeen\Core\Account();
        $u->setLoginName($_SERVER['PHP_AUTH_USER']);
        return $u;
    }

    /**
     * use new \locale language
     *
     * @param string $lang like fr_FR, en_US
     *
     * @throws \Exception
     */
    public static function setLanguage($lang)
    {
        $action = self::getCurrentAction();

        if (!$lang) {
            return;
        }
        if ($action) {
            $action->parent->param->SetVolatile("CORE_LANG", $lang);
            $action->parent->setVolatileParam("CORE_LANG", $lang);
        }
        $lang .= ".UTF-8";
        if (setlocale(LC_MESSAGES, $lang) === false) {
            throw new \Exception(sprintf(\ErrorCodeCORE::CORE0011, $lang));
        }
        setlocale(LC_CTYPE, $lang);
        setlocale(LC_MONETARY, $lang);
        setlocale(LC_TIME, $lang);
        //print $action->Getparam("CORE_LANG");
        $number = 0;
        $numberFile = sprintf("%s/locale/.gettextnumber", self::getRootDirectory());

        if (is_file($numberFile)) {
            $number = trim(@file_get_contents($numberFile));
            if ($number == "") {
                $number = 0;
            }
        }
        // @TODO  find another way
        // Reset enum traduction cache
        $a = null;

        $enumAttr = new \NormalAttribute("", "", "", "", "", "", "", "", "", "", "", "", $a, "", "", "");
        $enumAttr->resetEnum();

        $td = "main-catalog$number";

        putenv("LANG=" . $lang); // needed for old Linux kernel < 2.4
        putenv("LANGUAGE="); // no use LANGUAGE variable
        bindtextdomain($td, sprintf("%s/locale", self::getRootDirectory()));
        bind_textdomain_codeset($td, 'utf-8');
        textdomain($td);
        mb_internal_encoding('UTF-8');
        self::$language = $lang;
    }

    /**
     * Get current locale used
     *
     * @return string like fr_FR, en_US
     *
     */
    public static function getLanguage()
    {
        return self::$language;
    }

    protected static function _initCoreVolatileParam(\Application &$core)
    {
        $absindex = $core->getParam("CORE_URLINDEX");
        if ($absindex == '') {
            $absindex = "./";
        }
        $core_externurl = self::stripUrlSlahes($absindex);
        $core_mailaction = $core->getParam("CORE_MAILACTION");
        $core_mailactionurl = ($core_mailaction != '') ? ($core_mailaction)
            : ($core_externurl . "?app=FDL&action=OPENDOC&mode=view");

        $core->SetVolatileParam("CORE_EXTERNURL", $core_externurl);
        $core->SetVolatileParam("CORE_MAILACTIONURL", $core_mailactionurl);
    }

    public static function sudo(\Anakeen\Core\Account &$account)
    {
        self::$coreAction = self::getCurrentAction();
        if (!self::$coreAction) {
            throw new \Exception("CORE0017");
        }
        self::$coreUser = $account;

        self::$coreAction->parent->user = &self::$coreUser;
        self::$coreApplication =& self::$coreAction->parent;
        self::$coreAction->user = &self::$coreUser;
        if (self::$coreApplication->parent && self::$coreApplication->parent->id !== self::$coreApplication->id) {
            self::$coreApplication->parent->user = &self::$coreUser;
        }
    }

    /**
     * Delete double slashes in url path
     *
     * @param string $url
     *
     * @return string
     */
    protected static function stripUrlSlahes($url)
    {
        $pos = mb_strpos($url, '://');
        return mb_substr($url, 0, $pos + 3) . preg_replace('/\/+/u', '/', mb_substr($url, $pos + 3));
    }

    /**
     * @return \Anakeen\Core\Account|null
     */
    public static function getCurrentUser()
    {
        $cAction = self::getCurrentAction();
        if ($cAction) {
            return self::$coreUser = self::getCurrentAction()->user;
        }
        return null;
    }

    /**
     * @return \Anakeen\Core\Internal\Action|null
     */
    public static function getCurrentAction()
    {
        if (!self::$coreAction) {
            global $action;
            if ($action) {
                self::$coreAction =& $action;
                self::$coreApplication =& self::$coreAction->parent;
            }
        }
        return self::$coreAction;
    }

    /**
     * @return \Application|null
     */
    public static function getCurrentApplication()
    {
        return self::$coreAction->parent;
    }

    /**
     * return value of an global application parameter
     *
     * @brief must be in core or global type
     *
     * @param string $name param name
     * @param string $def  default value if value is empty
     *
     * @return string
     */
    public static function getApplicationParam($name, $def = "")
    {
        $action = self::getCurrentAction();
        if ($action) {
            return $action->getParam($name, $def);
        }
        // if context not yet initialized
        return self::getCoreParam($name, $def);
    }

    /**
     * return value of a parameter
     *
     * @brief must be in core or global type
     *
     * @param string $name param name
     * @param string $def  default value if value is empty
     *
     * @return string
     */
    public static function getCoreParam($name, $def = "")
    {
        if (($value = \Anakeen\Core\Internal\ApplicationParameterManager::_catchDeprecatedGlobalParameter($name)) !== null) {
            return $value;
        }
        if (empty(self::$coreParams)) {
            self::$coreParams = array();
            $tparams = array();
            try {
                \Anakeen\Core\DbManager::query(
                    "select name, val from paramv where (type = 'G') or (type='A' and appid = (select id from application where name ='CORE'));",
                    $tparams,
                    false,
                    false
                );

                foreach ($tparams as $p) {
                    self::$coreParams[$p['name']] = $p['val'];
                }
            } catch (\Dcp\Db\Exception $e) {
            }
        }
        if (array_key_exists($name, self::$coreParams) == false) {
            error_log(sprintf("parameter %s not found use %s instead", $name, $def));
            return $def;
        }
        return (self::$coreParams[$name] === null) ? $def : self::$coreParams[$name];
    }

    public static function getRootDirectory()
    {
        static $pubdir = null;
        if ($pubdir === null) {
            $pubdir = DEFAULT_PUBDIR;
        }
        return $pubdir;
    }


    /**
     * Get Application temporary directory
     * This directory is cleaned each days
     *
     * @param string $def
     *
     * @return string
     */
    public static function getTmpDir($def = '/tmp')
    {
        static $tmp;
        if (isset($tmp) && !empty($tmp)) {
            return $tmp;
        }
        $tmp = \Anakeen\Core\ContextManager::getApplicationParam('CORE_TMPDIR', $def);
        if (empty($tmp)) {
            if (empty($def)) {
                $tmp = './var/tmp';
            } else {
                $tmp = $def;
            }
        }

        if (substr($tmp, 0, 1) != '/') {
            $tmp = DEFAULT_PUBDIR . '/' . $tmp;
        }
        /* Try to create the directory if it does not exists */
        if (!is_dir($tmp)) {
            mkdir($tmp);
        }
        /* Add suffix, and try to create the sub-directory */
        $tmp = $tmp . '/ank';
        if (!is_dir($tmp)) {
            mkdir($tmp);
        }
        /* We ignore any failure in the directory creation
         * and return the expected tmp dir.
         * The caller will have to handle subsequent
         * errors...
        */
        return $tmp;
    }
}
