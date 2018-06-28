<?php

namespace Anakeen\Core;

use Anakeen\Core\Internal\ContextParameterManager;
use Anakeen\Core\Internal\GlobalParametersManager;
use Anakeen\Core\Utils\Gettext;
use Anakeen\Router\AuthenticatorManager;

class ContextManager
{


    /**
     * @var \Anakeen\Core\Internal\Session
     */
    protected static $session;

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
     * @var \Anakeen\Core\Account
     */
    protected static $originalUser = null;

    /**
     *
     * @param string $core_lang
     *
     * @return bool|array
     */
    public static function getLocaleConfig($core_lang = '')
    {
        if (empty($core_lang)) {
            $core_lang = self::getParameterValue("CORE_LANG", "fr_FR");
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

    public static function getLocales()
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
     * @param \Anakeen\Core\Account               $account
     * @param \Anakeen\Core\Internal\Session|null $session
     *
     * @throws \Dcp\Db\Exception
     * @throws \Exception
     */
    public static function initContext(\Anakeen\Core\Account $account, \Anakeen\Core\Internal\Session $session = null)
    {
        set_include_path(self::getRootDirectory() . PATH_SEPARATOR . get_include_path());


        if ($session) {
            self::$session = $session;
        }
        if (!self::$session) {
            self::$session = new \Anakeen\Core\Internal\Session();
        }


        self::$coreUser =& $account;
        GlobalParametersManager::initialize();

        self::setLanguage(self::getParameterValue("CORE_LANG", "fr_FR"));
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
            case \Anakeen\Core\Internal\Authenticator::AUTH_OK: // it'good, user is authentified
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
                $exception->setUserMessage(Gettext::___("Access not granted", "ank"));
                throw $exception;
        }
        $_SERVER['PHP_AUTH_USER'] = AuthenticatorManager::$auth->getAuthUser();
        // First control
        if (empty($_SERVER['PHP_AUTH_USER'])) {
            $exception = new \Anakeen\Router\Exception("User must be authenticated");
            $exception->setHttpStatus("403", "Forbidden");
            $exception->setUserMessage(Gettext::___("Access not granted", "ank"));
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
     * @return string the po header
     */
    public static function setLanguage($lang)
    {
        if (!$lang) {
            return "";
        }

        ContextParameterManager::setVolatile("CORE_LANG", $lang);

        if (strpos($lang, ".") === false) {
            $lang .= ".UTF-8";
        }
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

        EnumManager::resetEnum();

        $td = "main-catalog$number";

        putenv("LANG=" . $lang); // needed for old Linux kernel < 2.4
        putenv("LANGUAGE="); // no use LANGUAGE variable
        bindtextdomain($td, sprintf("%s/locale", self::getRootDirectory()));
        bind_textdomain_codeset($td, 'utf-8');
        textdomain($td);
        mb_internal_encoding('UTF-8');
        self::$language = $lang;

        // Load global function ___
        return Gettext::___("");
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

    /**
     * @param Account $account
     * @return Account previous account login
     * @throws \Exception
     */
    public static function sudo(\Anakeen\Core\Account &$account)
    {
        if (!self::$coreUser) {
            throw new \Exception("CORE0017");
        }
        if (self::$coreUser && !self::$originalUser) {
            self::$originalUser = self::$coreUser;
        }
        $previousUser = self::$coreUser;
        self::$coreUser = $account;

        return $previousUser;
    }

    public static function exitSudo()
    {
        if (self::$originalUser) {
            self::sudo(self::$originalUser);
        }
    }


    /**
     * @param bool $original use origin logged account even sudo is used
     * @return \Anakeen\Core\Account|null
     */
    public static function getCurrentUser(bool $original = false)
    {
        if (self::$coreUser) {
            if ($original === true && self::$originalUser) {
                return self::$originalUser;
            }
            return self::$coreUser;
        }
        return null;
    }


    /**
     * @return Internal\Session
     */
    public static function getSession()
    {
        return self::$session;
    }

    /**
     * display error to user and stop execution
     *
     * @param string $texterr the error message
     * @param bool   $exit    if false , no exit are pêrformed
     * @param string $code    error code (ref to error log)
     *
     * @throws \Dcp\Core\Exception
     * @api abort action execution
     * @return void
     */
    public static function exitError($texterr, $exit = true, $code = "")
    {
        if (!empty($_SERVER['HTTP_HOST'])) {
            $accept = $_SERVER['HTTP_ACCEPT'];
            $useHtml = ((!empty($accept) && preg_match("@\\btext/html\\b@", $accept)));

            if ($useHtml) {
                print \Dcp\Core\Utils\ErrorMessage::getHtml($texterr, $code);
            } else {
                $useJSON = ((!empty($accept) && preg_match("@\\bapplication/json\\b@", $accept)));
                if ($useJSON) {
                    header('Content-Type: application/json');
                    print \Dcp\Core\Utils\ErrorMessage::getJson($texterr, $code);
                } else {
                    header('Content-Type: text/plain');
                    print \Dcp\Core\Utils\ErrorMessage::getText($texterr, $code);
                }
            }
            if ($exit) {
                exit;
            }
        } else {
            throw new \Dcp\Core\Exception("CORE0001", $texterr);
        }
    }

    /**
     * @return \Anakeen\Core\Internal\Application|null
     */
    protected static function getCurrentApplication()
    {
        throw new \Exception(__METHOD__);
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
    public static function getParameterValue($name, $def = "")
    {
        return ContextParameterManager::getValue($name, $def);
    }


    public static function setParameterValue($name, $value)
    {
        ContextParameterManager::setValue($name, $value);
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
        $tmp = \Anakeen\Core\ContextManager::getParameterValue('CORE_TMPDIR', $def);
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

    public static function inMaintenance()
    {
        return file_exists(DEFAULT_PUBDIR . DIRECTORY_SEPARATOR . 'maintenance.lock');
    }
}
