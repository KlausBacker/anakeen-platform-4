<?php

namespace Dcp\Core;


class ContextManager
{
    /**
     * @var \Application
     */
    protected static $coreApplication = null;
    /**
     * @var \Action
     */
    protected static $coreAction = null;
    /**
     * @var \Account
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
     * @return bool|array
     */
    public static function getLocaleConfig($core_lang = '')
    {
        if (empty($core_lang)) {
            $core_lang = self::getApplicationParam("CORE_LANG", "fr_FR");
        }
        $lng = substr($core_lang, 0, 2);
        if (preg_match('#^[a-z0-9_\.-]+$#i', $core_lang) && file_exists(DEFAULT_PUBDIR."/locale/" . $lng . "/lang.php")) {
            include(DEFAULT_PUBDIR."/locale/" . $lng . "/lang.php");
        } else {
            include(DEFAULT_PUBDIR."/locale/fr/lang.php");
        }
        if (!isset($lang) || !isset($lang[$core_lang]) || !is_array($lang[$core_lang])) {
            return false;
        }
        return $lang[$core_lang];
    }

    function getLocales()
    {
        static $locales = null;

        if ($locales === null) {
            $lang = array();
            include ('CORE/lang.php');
            $locales = $lang;
        }
        return $locales;
    }

    /**
     * Initialise application context
     * @param string $appName
     * @param string $actionName
     */
    public static function initContext(\Account $account, $appName = "", $actionName = "", \Session $session=null)
    {
        global $action;
        set_include_path(self::getRootDirectory() . PATH_SEPARATOR . get_include_path());

        $coreApplication = new \Application();
        $coreApplication->user = &$account;
        self::$coreUser = &$account;
        $coreApplication->Set("CORE", $CoreNull);
        $coreApplication->session = $session;
        if (!$coreApplication->session) {
            $coreApplication->session=new \Session();
        }

        self::_initCoreVolatileParam($coreApplication);
        if ($appName && $appName !== "CORE") {
            $application = new \Application();
            $application->set($appName, $coreApplication);
            self::$coreApplication = $application;
        } else {
            self::$coreApplication = $coreApplication;
        }

        self::$coreAction = new \Action();
        $action=new \Action();
        self::$coreAction = &$action;
        self::$coreAction->Set($actionName, self::$coreApplication);
        self::$coreAction->user=&$account;


        self::setLanguage(self::getApplicationParam("CORE_LANG", "fr_FR"));

    }

    public static function recordContext(\Account $account, \Action $action=null) {
         self::$coreUser = &$account;
         if ($action) {
             self::$coreAction = &$action;
             self::$coreApplication = $action->parent;
         }
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
        self::$language=$lang;
    }

    /**
     * Get current locale used
     *
     * @return string like fr_FR, en_US
     *
     */
    public static function getLanguage() {
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
        $core_mailactionurl = ($core_mailaction != '') ? ($core_mailaction) : ($core_externurl . "?app=FDL&action=OPENDOC&mode=view");

        $core->SetVolatileParam("CORE_EXTERNURL", $core_externurl);
        $core->SetVolatileParam("CORE_MAILACTIONURL", $core_mailactionurl);
    }

    public static function sudo(\Account &$account)
    {

        self::$coreAction=self::getCurrentAction();
        if (!self::$coreAction) {
            throw new \Exception("CORE0017");
        }
        self::$coreUser = $account;

        self::$coreAction->parent->user = &self::$coreUser;
        self::$coreApplication=&self::$coreAction->parent;
        self::$coreAction->user = &self::$coreUser;
        if (self::$coreApplication->parent && self::$coreApplication->parent->id !== self::$coreApplication->id) {
            self::$coreApplication->parent->user = &self::$coreUser;
        }
    }

    /**
     * Delete double slashes in url path
     * @param string $url
     * @return string
     */
    protected static function stripUrlSlahes($url)
    {
        $pos = mb_strpos($url, '://');
        return mb_substr($url, 0, $pos + 3) . preg_replace('/\/+/u', '/', mb_substr($url, $pos + 3));
    }

    /**
     * @return \Account
     */
    public static function getCurrentUser()
    {
         return self::$coreUser=self::getCurrentAction()->user;
    }

    /**
     * @return \Action|null
     */
    public static function getCurrentAction()
    {
        if (! self::$coreAction) {
            global $action;
            if ($action) {
                self::$coreAction=&$action;
                self::$coreApplication=&self::$coreAction->parent;
            }
        }
        return self::$coreAction;
    }
    /**
     * @return \Application|null
     */
    public static function getCurrentApplication()
    {
        return self::$coreApplication;
    }

    /**
     * return value of an global application parameter
     *
     * @brief must be in core or global type
     * @param string $name param name
     * @param string $def default value if value is empty
     *
     * @return string
     */
    public static function getApplicationParam($name, $def = "")
    {
        $action = self::getCurrentAction();
        if ($action) return $action->getParam($name, $def);
        // if context not yet initialized
        return self::getCoreParam($name, $def);
    }

    /**
     * return value of a parameter
     *
     * @brief must be in core or global type
     * @param string $name param name
     * @param string $def default value if value is empty
     *
     * @return string
     */
    public static function getCoreParam($name, $def = "")
    {
        if (($value = \ApplicationParameterManager::_catchDeprecatedGlobalParameter($name)) !== null) {
            return $value;
        }
        if (empty(self::$coreParams)) {
            self::$coreParams = array();
            $tparams = array();
            try {
                \Dcp\Core\DbManager::query("select name, val from paramv where (type = 'G') or (type='A' and appid = (select id from application where name ='CORE'));",
                    $tparams, false, false);

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
}