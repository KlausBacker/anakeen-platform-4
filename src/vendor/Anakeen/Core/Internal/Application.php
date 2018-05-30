<?php

namespace Anakeen\Core\Internal;

use Anakeen\Core\DbManager;
use Anakeen\LogManager;

/**
 * Application manager
 *
 * @class Application
 *
 */
class Application extends DbObj
{
    public $fields
        = array(
            "id",
            "name",
            "short_name",
            "description",
            "access_free", //@deprecated
            "available",
            "icon",
            "displayable",
            "with_frame",
            "childof",
            "objectclass", //@deprecated
            "ssl", //@deprecated
            "machine", //@deprecated
            "iorder",
            "tag"
        );
    /**
     * @var int application identifier
     */
    public $id;
    public $name;
    public $short_name;
    public $description;
    /**
     * @deprecated
     * @var $access_free
     */
    public $access_free;
    public $available;
    public $icon;
    public $displayable;
    public $with_frame;
    public $childof;
    /**
     * @deprecated
     * @var $objectclass
     */
    public $objectclass;
    /**
     * @deprecated
     * @var $ssl
     */
    public $ssl;
    /**
     * @deprecated
     * @var $machine
     */
    public $machine;
    public $iorder;
    public $tag;
    public $id_fields
        = array(
            "id"
        );
    public $rootdir = '';
    public $fulltextfields
        = array(
            "name",
            "short_name",
            "description"
        );
    public $sqlcreate
        = '
create table application ( 	id 	int not null,
     		primary key (id),
			name 	    text not null,
			short_name text,
			description text ,
			access_free  char,
			available  char,
                        icon text,
                        displayable char,
                        with_frame char,
                        childof text,
                        objectclass char,
                        ssl char,
                        machine text,
                        iorder int,
                        tag text);
create index application_idx1 on application(id);
create index application_idx2 on application(name);
create sequence SEQ_ID_APPLICATION start 10;
';

    public $dbtable = "application";

    public $def
        = array(
            "criteria" => "",
            "order_by" => "name"
        );

    public $criterias
        = array(
            "name" => array(
                "libelle" => "Nom",
                "type" => "TXT"
            )
        );
    /**
     * @var \Anakeen\Core\Internal\Application
     */
    public $parent = null;
    /**
     * @var \Anakeen\Core\Internal\Session
     */
    public $session = null;
    /**
     * @var \Anakeen\Core\Account
     */
    public $user = null;
    /**
     * @var \Anakeen\Core\Internal\Style
     */
    public $style;
    /**
     * @var \Anakeen\Core\Internal\Param
     */
    public $param;
    /**
     * @var \Permission
     */
    public $permission = null; // permission object

    /**
     * @var \Anakeen\Core\Internal\Log
     */
    public $log = null;
    public $jsref = array();
    public $jscode = array();
    public $logmsg = array();
    /**
     * true if application is launched from admin context
     *
     * @var bool
     */
    protected $adminMode = false;

    public $cssref = array();
    public $csscode = array();
    protected $publicdir;

    /**
     * Application constructor.
     *
     * @param string          $dbaccess
     * @param string|string[] $id
     * @param string|array    $res
     * @param int             $dbid
     */
    public function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0)
    {
        parent::__construct($dbaccess, $id, $res, $dbid);
        $this->rootdir = DEFAULT_PUBDIR . "/Apps";
        $this->publicdir = PUBLIC_DIR;
    }

    /**
     * initialize  Application object
     *
     * @param string                                    $name            application name to set
     * @param \Anakeen\Core\Internal\Application|string $parent          the parent object (generally CORE app) : empty string if no parent
     * @param string                                    $session         parent session
     * @param bool                                      $autoinit        set to true to auto create app if not exists yet
     *
     * @param bool                                      $verifyAvailable set to true to not exit when unavailable action
     *
     * @return string error message
     * @throws \Dcp\Core\Exception if application not exists
     * @throws \Dcp\Db\Exception
     * @code
     * $CoreNull = "";
     * $core = new \Anakeen\Core\Internal\Application();
     * $core->Set("CORE", $CoreNull); // init core application from nothing
     * $core->session = new \Anakeen\Core\Internal\Session();
     * $core->session->set();
     * $one = new \Anakeen\Core\Internal\Application();
     * $one->set("ONEFAM", $core, $core->session);// init ONEFAM application from CORE
     *
     * @endcode
     *
     */
    public function set($name, &$parent = null, $session = "", $autoinit = false, $verifyAvailable = true)
    {
        LogManager::debug("Entering : Set application to $name");

        $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, self::class);
        $query->order_by = "";
        $query->criteria = "name";
        $query->operator = "=";
        $query->string = "'" . pg_escape_string($name) . "'";
        $list = $query->Query(0, 0, "TABLE");
        if ($query->nb != 0) {
            $this->affect($list[0]);
            LogManager::debug("Set application to $name");
            if (!isset($parent)) {
                LogManager::debug("Parent not set");
            }
        } else {
            if ($autoinit) {
                // Init the database with the app file if it exists
                $this->InitApp($name);
                if ($parent != "") {
                    $this->parent = &$parent;
                    if ($this->name == "") {
                        printf("Application name %s not found", $name);
                        exit;
                    } elseif (!empty($_SERVER['HTTP_HOST'])) {
                        Redirect($this, $this->name, "");
                    }
                } else {
                    global $_SERVER;
                    if (!empty($_SERVER['HTTP_HOST'])) {
                        Header("Location: " . $_SERVER['HTTP_REFERER']);
                    }
                }
            } else {
                $e = new \Dcp\Core\Exception("CORE0004", $name);
                $e->addHttpHeader('HTTP/1.0 404 Application not found');
                throw $e;
            }
        }

        if ($parent !== null && $this !== $parent) {
            $this->parent = &$parent;
        }
        if (is_object($this->parent) && isset($this->parent->session)) {
            $this->session = $this->parent->session;
            if (isset($this->parent->user) && is_object($this->parent->user)) {
                $this->user = $this->parent->user;
            }
        }

        if ($session != "") {
            $this->SetSession($session);
        }
        $this->param = new \Anakeen\Core\Internal\Param($this->dbaccess);
        $style = false;


        if ($style) {
            $this->InitStyle(false, $style);
        } else {
            $this->InitStyle();
        }


        $this->param->SetKey($this->id, isset($this->user->id) ? $this->user->id : false, $this->style->name);
        if ($verifyAvailable && $this->available === "N") {
            // error
            $e = new \Dcp\Core\Exception("CORE0007", $name);
            $e->addHttpHeader('HTTP/1.0 503 Application unavailable');
            throw $e;
        }
        $this->permission = null;

        return '';
    }

    public function complete()
    {
    }

    public function setSession(&$session)
    {
        $this->session = $session;
        // Set the user if possible
        if (is_object($this->session)) {
            if ($this->session->userid != 0) {
                LogManager::debug("Get user on " . $this->dbaccess);
                $this->user = new \Anakeen\Core\Account($this->dbaccess, $this->session->userid);
            } else {
                LogManager::debug("User not set ");
            }
        }
    }

    public function preInsert()
    {
        if ($this->Exists($this->name)) {
            return "Ce nom d'application existe deja...";
        }
        if ($this->name == "CORE") {
            $this->id = 1;
        } else {
            $this->query("select nextval ('seq_id_application')");
            $arr = $this->fetchArray(0);
            $this->id = $arr["nextval"];
        }
        return '';
    }

    public function preUpdate()
    {
        if ($this->dbid == -1) {
            return false;
        }
        if ($this->Exists($this->name, $this->id)) {
            return "Ce nom d'application existe deja...";
        }
        return '';
    }

    /**
     * Verify an application name exists
     *
     * @param string $app_name       application reference name
     * @param int    $id_application optional numeric id to verify if not itself
     *
     * @return bool
     */
    public function exists($app_name, $id_application = 0)
    {
        LogManager::debug("Exists $app_name ?");
        $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, self::class);
        $query->order_by = "";
        $query->criteria = "";

        if ($id_application != '') {
            $query->basic_elem->sup_where = array(
                "name='$app_name'",
                "id!=$id_application"
            );
        } else {
            $query->criteria = "name";
            $query->operator = "=";
            $query->string = "'" . $app_name . "'";
        }

        $r = $query->Query(0, 0, "TABLE");

        return ($query->nb > 0) ? $r[0]["id"] : false;
    }

    /**
     * Strip the pubdir/wpub directory from a file pathname
     *
     * @param string $pathname the file pathname
     *
     * @return string file pathname without the root dir
     */
    private function stripRootDir($pathname)
    {
        if (substr($pathname, 0, strlen($this->rootdir)) === $this->rootdir) {
            $pathname = substr($pathname, strlen($this->rootdir) + 1);
        }

        return $pathname;
    }

    /**
     * Try to resolve a JS/CSS reference to a supported location
     *
     * @param string $ref the JS/CSS reference
     *
     * @return string the resolved location of the reference or an empty string on failure
     */
    private function resolveResourceLocation($ref)
    {
        if (strstr($ref, '../') !== false) {
            return '';
        }
        if (strstr($ref, '?app=') !== false) {
            // no search server file for urls
            return $ref;
        }
        /* Resolve through getLayoutFile */
        $location = $this->GetLayoutFile($ref);
        if ($location != '') {
            return $this->stripRootDir($location);
        }
        /* Try "APP:file.extension" notation */
        if (preg_match('/^(?P<appname>[a-z][a-z0-9_-]*):(?P<filename>.*)$/i', $ref, $m)) {
            $location = sprintf('%s/%s/Layout/%s', $this->publicdir, $m['appname'], $m['filename']);
            if (is_file($location)) {
                return sprintf('%s/Layout/%s', $m['appname'], $m['filename']);
            }
            // Fallback for legacy : return css/js from Apps/Layout
            $location = sprintf('%s/%s/Layout/%s', $this->rootdir, $m['appname'], $m['filename']);

            if (is_file($location)) {
                return sprintf('/assets/%s', urlencode($ref));
            }
        }
        /* Try hardcoded locations */
        foreach (array($ref, sprintf("%s/Layout/%s", $this->name, $ref)) as $filename) {
            if (is_file(sprintf("%s/%s", $this->rootdir, $filename))) {
                return $filename;
            }
        }
        /* Detect URLs */
        $pUrl = parse_url($ref);
        if (isset($pUrl['scheme']) || isset($pUrl['query'])) {
            return $ref;
        }

        if (is_file($ref)) {
            return $ref;
        }
        if (is_file($this->publicdir . "/" . $ref)) {
            return $ref;
        }
        /* TODO : update with application log class */
        LogManager::error(__METHOD__ . " Unable to identify the ref $ref");

        return '';
    }

    /**
     * Add a resource (JS/CSS) to the page
     *
     * @param string  $type      'js' or 'css'
     * @param string  $ref       the resource reference
     * @param boolean $needparse should the resource be parsed (default false)
     * @param string  $packName
     *
     * @return string resource location
     */
    public function addRessourceRef($type, $ref, $needparse, $packName)
    {
        /* Try to attach the resource to the parent app */
        if ($this->hasParent()) {
            $ret = $this->parent->AddRessourceRef($type, $ref, $needparse, $packName);
            if ($ret !== '') {
                return $ret;
            }
        }

        $resourceLocation = $this->getResourceLocation($type, $ref, $needparse, $packName, true);
        if (!$resourceLocation) {
            $wng = sprintf(_("Cannot find %s resource file"), $ref);
            $this->addLogMsg($wng);
            LogManager::warning($wng);
            $resourceLocation = sprintf("Ressource %s not found", $ref);
        }

        if (strpos($resourceLocation, "?") === false) {
            $resourceLocation .= "?ws=" . $this->getParam("WVERSION");
        }
        if ($type == 'js') {
            $this->jsref[$resourceLocation] = $resourceLocation;
        } elseif ($type == 'css') {
            $this->cssref[$resourceLocation] = $resourceLocation;
        } else {
            return '';
        }

        return $resourceLocation;
    }

    /**
     * Get resourceLocation with cache handling
     *
     * @param string $type      (js|css)
     * @param string $ref       path or URI of the resource
     * @param bool   $needparse need to parse
     * @param string $packName  use it to pack all the ref with the same packName into a single file
     * @param bool   $fromAdd   (do not use this param) true only if you call it from addRessourceRef function
     *
     * @return string new \location
     */
    private function getResourceLocation($type, $ref, $needparse, $packName, $fromAdd = false)
    {
        static $firstPack = array();
        $resourceLocation = '';

        $key = isset($this->session) ? $this->session->getUKey(\Anakeen\Core\ContextManager::getApplicationParam("WVERSION"))
            : uniqid(\Anakeen\Core\ContextManager::getApplicationParam("WVERSION"));
        if ($packName) {
            $resourcePackParseLocation = sprintf("?app=CORE&amp;action=CORE_CSS&amp;type=%s&amp;ukey=%s&amp;pack=%s", $type, $key, $packName);
            $resourcePackNoParseLocation = sprintf("pack.php?type=%s&amp;pack=%s&amp;wv=%s", $type, $packName, \Anakeen\Core\ContextManager::getApplicationParam("WVERSION"));

            if (!isset($firstPack[$packName])) {
                $packSession = array();
                $firstPack[$packName] = true;
            } else {
                $packSession = ($this->session ? $this->session->Read("RSPACK_" . $packName) : array());
                if (!$packSession) {
                    $packSession = array();
                }
            }
            $packSession[$ref] = array(
                "ref" => $ref,
                "needparse" => $needparse
            );
            if ($this->session) {
                $this->session->Register("RSPACK_" . $packName, $packSession);
            }

            if ($needparse) {
                if ($fromAdd) {
                    if ($type == "js") {
                        unset($this->jsref[$resourcePackNoParseLocation]);
                    } elseif ($type == "css") {
                        unset($this->cssref[$resourcePackNoParseLocation]);
                    }
                }
                $resourceLocation = $resourcePackParseLocation;
            } else {
                $hasParseBefore = (($type === "js") && isset($this->jsref[$resourcePackParseLocation]));
                if (!$hasParseBefore) {
                    $hasParseBefore = (($type === "css") && isset($this->cssref[$resourcePackParseLocation]));
                }
                if (!$hasParseBefore) {
                    $resourceLocation = $resourcePackNoParseLocation;
                }
            }
        } elseif ($needparse) {
            $resourceLocation = "?app=CORE&amp;action=CORE_CSS&amp;ukey=" . $key . "&amp;layout=" . $ref . "&amp;type=" . $type;
        } else {
            $location = $this->resolveResourceLocation($ref);
            if ($location != '') {
                $resourceLocation = (strpos($location, '?') !== false) ? $location : $location . '?wv=' . \Anakeen\Core\ContextManager::getApplicationParam("WVERSION");
            }
        }

        return $resourceLocation;
    }

    /**
     * Get dynacase CSS link
     *
     * @api Get the src of a CSS with dynacase cache
     *
     * @param string $ref       path, or URL, or filename (if in the current application), or APP:filename
     * @param bool   $needparse if true will be parsed by the template engine (false by default)
     * @param string $packName  use it to pack all the ref with the same packName into a single file
     *
     * @return string the src of the CSS or "" if non existent ref
     */
    public function getCssLink($ref, $needparse = null, $packName = '')
    {
        if (substr($ref, 0, 2) == './') {
            $ref = substr($ref, 2);
        }
        $styleParseRule = $this->detectCssParse($ref, $needparse);
        $rl = $this->getResourceLocation('css', $ref, $styleParseRule, $packName);
        if (!$rl) {
            $msg = sprintf(_("Cannot find %s resource file"), $ref);
            $this->addLogMsg($msg);
            LogManager::warning($msg);
        }
        return $rl;
    }

    /**
     * Get dynacase JS link
     *
     * @api Get the src of a JS with dynacase cache
     *
     * @param string $ref       path, or URL, or filename (if in the current application), or APP:filename
     * @param bool   $needparse if true will be parsed by the template engine (false by default)
     * @param string $packName  use it to pack all the ref with the same packName into a single file
     *
     * @return string the src of the JS or "" if ref not exists
     */
    public function getJsLink($ref, $needparse = false, $packName = '')
    {
        if (substr($ref, 0, 2) == './') {
            $ref = substr($ref, 2);
        }
        $rl = $this->getResourceLocation('js', $ref, $needparse, $packName);
        if (!$rl) {
            $msg = sprintf(_("Cannot find %s resource file"), $ref);
            $this->addLogMsg($msg);
            LogManager::warning($msg);
        }
        return $rl;
    }

    /**
     * Add a CSS in an action
     *
     * Use this method to add a CSS in an action that use the zone [CSS:REF] and the template engine
     *
     * @api Add a CSS in an action
     *
     * @param string $ref       path, or URL, or filename (if in the current application), or APP:filename
     * @param bool   $needparse if true will be parsed by the template engine (false by default)
     * @param string $packName  use it to pack all the ref with the same packName into a single file
     *
     * @throws \Dcp\Style\Exception
     * @return string the path of the added ref or "" if the ref is not valid
     */
    public function addCssRef($ref, $needparse = null, $packName = '')
    {
        $styleParseRule = $this->detectCssParse($ref, $needparse);

        if (substr($ref, 0, 2) == './') {
            $ref = substr($ref, 2);
        }
        return $this->AddRessourceRef('css', $ref, $styleParseRule, $packName);
    }

    private function detectCssParse($ref, $askParse)
    {
        $needparse = $askParse;
        $currentFileRule = $this->style->getRule('css', $ref);
        if (is_array($currentFileRule)) {
            if (isset($currentFileRule['flags']) && ($currentFileRule['flags'] & \Anakeen\Core\Internal\Style::RULE_FLAG_PARSE_ON_RUNTIME)) {
                if (isset($currentFileRule['runtime_parser']) && is_array($currentFileRule['runtime_parser']) && isset($currentFileRule['runtime_parser']['className'])
                    && null !== $currentFileRule['parse_on_runtime']['className']) {
                    throw new \Dcp\Style\Exception("STY0007", 'custom parse_on_runtime class is not supported yet');
                }
                $parseOnLoad = true;
                if ((null !== $needparse) && ($parseOnLoad !== $needparse)) {
                    LogManager::warning(sprintf(
                        "%s was added with needParse to %s but style has a rule saying %s",
                        $ref,
                        var_export($needparse, true),
                        var_export($parseOnLoad, true)
                    ));
                }
                $needparse = $parseOnLoad;
            }
        }
        $needparse = $needparse ? true : false;

        return $needparse;
    }

    /**
     * Get the current CSS ref of the current action
     *
     * @return string[]
     */
    public function getCssRef()
    {
        if ($this->hasParent()) {
            return ($this->parent->GetCssRef());
        } else {
            return ($this->cssref);
        }
    }

    /**
     * Add a JS in an action
     *
     * Use this method to add a JS in an action that use the zone [JS:REF] and the template engine
     *
     * @api Add a JS in an action
     *
     * @param string $ref       path to a js, or URL to a js, or js file name (if in the current application), or APP:jsfilename
     * @param bool   $needparse if true will be parsed by the template engine (false by default)
     * @param string $packName  use it to pack all the ref with the same packName into a single file
     *
     * @return string the path of the added ref or "" if the ref is not valid
     */
    public function addJsRef($ref, $needparse = false, $packName = '')
    {
        if (substr($ref, 0, 2) == './') {
            $ref = substr($ref, 2);
        }
        return $this->AddRessourceRef('js', $ref, $needparse, $packName);
    }

    /**
     * Get the js ref array of the current action
     *
     * @return string[] array of location
     */
    public function getJsRef()
    {
        if ($this->hasParent()) {
            return ($this->parent->GetJsRef());
        } else {
            return ($this->jsref);
        }
    }

    /**
     * Add a JS code in an action
     * Use this method to add a JS in an action that use the zone [JS:REF] and the template engine
     * (beware use protective ; because all the addJsCode are concatened)
     *
     * @api Add a JS code in an action
     *
     * @param string $code code to add
     *
     * @return void
     */
    public function addJsCode($code)
    {
        // Js Code are stored in the top level application
        if ($this->hasParent()) {
            $this->parent->AddJsCode($code);
        } else {
            $this->jscode[] = $code;
        }
    }

    /**
     * Get the js code of the current action
     *
     * @return string[]
     */
    public function getJsCode()
    {
        if ($this->hasParent()) {
            return ($this->parent->GetJsCode());
        } else {
            return ($this->jscode);
        }
    }

    /**
     * Add a CSS code in an action
     * Use this method to add a CSS in an action that use the zone [CSS:REF] and the template engine
     *
     * @api Add a CSS code in an action
     *
     * @param string $code code to add
     *
     * @return void
     */
    public function addCssCode($code)
    {
        // Css Code are stored in the top level application
        if ($this->hasParent()) {
            $this->parent->AddCssCode($code);
        } else {
            $this->csscode[] = $code;
        }
    }

    /**
     * Get the current CSS code of the current action
     *
     * @return string[]
     */
    public function getCssCode()
    {
        if ($this->hasParent()) {
            return ($this->parent->GetCssCode());
        } else {
            return ($this->csscode);
        }
    }

    /**
     * Add message to log (syslog)
     * The message is also displayed in the console of the web interface
     *
     * @param string|string[] $code message to add to log
     * @param int    $cut  truncate message longer than this length (set to <= 0 to not truncate the message)(default is 0).
     */
    public function addLogMsg($code, $cut = 0)
    {
        if ($code == "") {
            return;
        }
        // Js Code are stored in the top level application
        if ($this->hasParent()) {
            $this->parent->AddLogMsg($code, $cut);
        } else {
            if ($this->session) {
                $logmsg = $this->session->read("logmsg", array());
                if (is_array($code)) {
                    $code["stack"] = getDebugStack(4);
                    $logmsg[] = json_encode($code);
                } else {
                    $logmsg[] = strftime("%H:%M - ") . str_replace("\n", "\\n", (($cut > 0) ? mb_substr($code, 0, $cut) : $code));
                }
                $this->session->register("logmsg", $logmsg);

                if (is_array($code)) {
                    $code = print_r($code, true);
                }
                LogManager::info($code);
            } else {
                error_log($code);
            }
        }
    }

    /**
     * send a message to the user interface
     *
     * @param string $code message
     *
     * @return void
     */
    public function addWarningMsg($code)
    {
        if (($code == "") || ($code == "-")) {
            return;
        }
        // Js Code are stored in the top level application
        if ($this->hasParent()) {
            $this->parent->addWarningMsg($code);
        } else {
            if (!empty($_SERVER['HTTP_HOST']) && $this->session) {
                $logmsg = $this->session->read("warningmsg", array());
                $logmsg[] = $code;
                $this->session->register("warningmsg", $logmsg);
            } else {
                error_log("dcp warning: $code");
            }
        }
    }

    /**
     * Get log text messages
     *
     * @return array
     */
    public function getLogMsg()
    {
        return ($this->session ? ($this->session->read("logmsg", array())) : array());
    }

    public function clearLogMsg()
    {
        if ($this->session) {
            $this->session->unregister("logmsg");
        }
    }

    /**
     * Get warning texts
     *
     * @return array
     */
    public function getWarningMsg()
    {
        return ($this->session ? ($this->session->read("warningmsg", array())) : array());
    }

    public function clearWarningMsg()
    {
        if ($this->session) {
            $this->session->unregister("warningmsg");
        }
    }

    /**
     * mark the application as launched from admin context
     *
     * @param bool $enable true to enable admin mode, false to disable it
     */
    public function setAdminMode($enable = true)
    {
        if ($this->hasParent()) {
            $this->parent->setAdminMode($enable);
        } else {
            $this->adminMode = ($enable ? true : false);
        }
    }

    /**
     * @return bool true if application is launched from admin context
     */
    public function isInAdminMode()
    {
        if ($this->hasParent()) {
            return $this->parent->isInAdminMode();
        }
        return $this->adminMode === true || $this->user->id == \Anakeen\Core\Account::ADMIN_ID;
    }

    /**
     * Test permission for current user in current application
     *
     * @param string $acl_name acl name to test
     * @param string $app_name application if test for other application
     * @param bool   $strict   to not use substitute account information
     *
     * @return bool true if permission granted
     */
    public function hasPermission($acl_name, $app_name = "", $strict = false)
    {
        if (\Anakeen\Core\Internal\Action::ACCESS_FREE == $acl_name) {
            return true;
        }
        if (!isset($this->user) || !is_object($this->user)) {
            LogManager::warning("Action {$this->parent->name}:{$this->name} requires authentification");
            return false;
        }
        if ($this->user->id == 1) {
            return true;
        } // admin can do everything
        if ($app_name == "") {
            $acl = new \Acl($this->dbaccess);
            if (!$acl->Set($acl_name, $this->id)) {
                LogManager::warning("Acl $acl_name not available for App $this->name");
                return false;
            }
            if (!$this->permission) {
                $permission = new \Permission($this->dbaccess, array(
                    $this->user->id,
                    $this->id
                ));
                if (!$permission->IsAffected()) { // case of no permission available
                    $permission->Affect(array(
                        "id_user" => $this->user->id,
                        "id_application" => $this->id
                    ));
                }
                $this->permission = &$permission;
            }

            return ($this->permission->HasPrivilege($acl->id, $strict));
        } else {
            // test permission for other application
            if (!is_numeric($app_name)) {
                $appid = $this->GetIdFromName($app_name);
            } else {
                $appid = $app_name;
            }

            $wperm = new \Permission($this->dbaccess, array(
                $this->user->id,
                $appid
            ));
            if ($wperm->isAffected()) {
                $acl = new \Acl($this->dbaccess);
                if (!$acl->Set($acl_name, $appid)) {
                    LogManager::warning("Acl $acl_name not available for App $this->name");
                    return false;
                } else {
                    return ($wperm->HasPrivilege($acl->id, $strict));
                }
            }
        }
        return false;
    }

    /**
     * create style parameters
     *
     * @param bool   $init
     * @param string $useStyle
     */
    public function initStyle($init = true, $useStyle = '')
    {
        if ($init == true) {
            if (isset($this->user)) {
                $pstyle = new \Anakeen\Core\Internal\Param($this->dbaccess, array(
                    "STYLE",
                    \Anakeen\Core\Internal\Param::PARAM_USER . $this->user->id,
                    "1"
                ));
            } else {
                $pstyle = new \Anakeen\Core\Internal\Param($this->dbaccess, array(
                    "STYLE",
                    \Anakeen\Core\Internal\Param::PARAM_USER . \Anakeen\Core\Account::ANONYMOUS_ID,
                    "1"
                ));
            }
            if (!$pstyle->isAffected()) {
                $pstyle = new \Anakeen\Core\Internal\Param($this->dbaccess, array(
                    "STYLE",
                    \Anakeen\Core\Internal\Param::PARAM_APP,
                    "1"
                ));
            }

            $style = $pstyle->val;
            $this->style = new \Anakeen\Core\Internal\Style($this->dbaccess, $style);

            $this->style->Set($this);
        } else {
            $style = ($useStyle) ? $useStyle : $this->getParam("STYLE");
            $this->style = new \Anakeen\Core\Internal\Style($this->dbaccess, $style);

            $this->style->Set($this);
        }
    }

    public function setLayoutVars($lay)
    {
        if ($this->hasParent()) {
            $this->parent->SetLayoutVars($lay);
        }
    }

    public function getRootApp()
    {
        if ($this->parent == "") {
            return ($this);
        } else {
            return ($this->parent->GetRootApp());
        }
    }

    public function getImageFile($img)
    {
        return $this->rootdir . "/" . $this->getImageLink($img);
    }

    public $noimage = "CORE/Images/core-noimage.png";

    /**
     * get image url of an application
     * can also get another image by search in Images general directory
     *
     * @api get image url of an application
     *
     * @param string $img         image filename
     * @param bool   $detectstyle to use theme image instead of original
     * @param int    $size        to use image with another width (in pixel) - null is original size
     *
     * @return string url to download image
     */
    public function getImageLink($img, $detectstyle = true, $size = null)
    {
        static $cacheImgUrl = array();

        $cacheIndex = $img . $size;
        if (isset($cacheImgUrl[$cacheIndex])) {
            return $cacheImgUrl[$cacheIndex];
        }
        if ($img != "") {
            // try style first
            if ($detectstyle) {
                $url = $this->style->GetImageUrl($img, "");
                if ($url != "") {
                    if ($size !== null) {
                        $url = 'resizeimg.php?img=' . urlencode($url) . '&size=' . $size;
                    }
                    $cacheImgUrl[$cacheIndex] = $url;
                    return $url;
                }
            }
            // try application
            if (file_exists($this->publicdir . "/" . $this->name . "/Images/" . $img)) {
                $url = $this->name . "/Images/" . $img;
                if ($size !== null) {
                    $url = 'resizeimg.php?img=' . urlencode($url) . '&size=' . $size;
                }
                $cacheImgUrl[$cacheIndex] = $url;
                return $url;
            } else { // perhaps generic application
                if (($this->childof != "") && (file_exists($this->publicdir . "/" . $this->childof . "/Images/" . $img))) {
                    $url = $this->childof . "/Images/" . $img;
                    if ($size !== null) {
                        $url = 'resizeimg.php?img=' . urlencode($url) . '&size=' . $size;
                    }
                    $cacheImgUrl[$cacheIndex] = $url;
                    return $url;
                } elseif (file_exists($this->publicdir . "/Images/" . $img)) {
                    $url = "Images/" . $img;
                    if ($size !== null) {
                        $url = 'resizeimg.php?img=' . urlencode($url) . '&size=' . $size;
                    }
                    $cacheImgUrl[$cacheIndex] = $url;
                    return $url;
                }
            }
            // try in parent
            if ($this->parent != "") {
                $url = $this->parent->getImageLink($img);
                if ($size !== null) {
                    $url = 'resizeimg.php?img=' . urlencode($url) . '&size=' . $size;
                }
                $cacheImgUrl[$cacheIndex] = $url;
                return $url;
            }
        }
        if ($size !== null) {
            return 'resizeimg.php?img=' . urlencode($this->noimage) . '&size=' . $size;
        }
        $this->addLogMsg("No find image \"$img\"");
        return $this->noimage;
    }




    /**
     * get file path layout from layout name
     *
     * @param string $layname
     *
     * @return string file path
     */
    public function getLayoutFile($layname)
    {
        if (strstr($layname, '..')) {
            return ""; // not authorized
        }
        $file = $this->style->GetLayoutFile($layname, "");
        if ($file != "") {
            return $file;
        }

        $laydir = $this->rootdir . "/" . $this->name . "/Layout/";
        $file = $laydir . $layname; // default file
        if (file_exists($file)) {
            return ($file);
        } else {
            // perhaps generic application
            $file = $this->rootdir . "/" . $this->childof . "/Layout/$layname";
            if (file_exists($file)) {
                return ($file);
            }
        }
        if ($this->parent != "") {
            return ($this->parent->GetLayoutFile($layname));
        }
        return ("");
    }

    public function OldGetLayoutFile($layname)
    {
        $file = $this->rootdir . "/" . $this->name . "/Layout/" . $layname;
        if (file_exists($file)) {
            $file = $this->style->GetLayoutFile($layname, $file);
            return ($file);
        }
        if ($this->parent != "") {
            return ($this->parent->GetLayoutFile($layname));
        }
        return ("");
    }

    /**
     * affect new \value to an application parameter
     *
     * @see \Anakeen\Core\Internal\ParameterManager to easily manage application parameters
     *
     * @param string          $key parameter id
     * @param string|string[] $val parameter value
     */
    public function setParam($key, $val)
    {
        if (is_array($val)) {
            if (isset($val["global"]) && $val["global"] == "Y") {
                $type = \Anakeen\Core\Internal\Param::PARAM_GLB;
            } else {
                $type = \Anakeen\Core\Internal\Param::PARAM_APP;
            }
            $this->param->Set($key, $val["val"], $type, $this->id);
        } else { // old method
            $this->param->Set($key, $val, \Anakeen\Core\Internal\Param::PARAM_APP, $this->id);
        }
    }

    /**
     * set user parameter for current user
     *
     * @see \Anakeen\Core\Internal\ParameterManager to easily manage application parameters
     *
     * @param string $key parameter identifier
     * @param string $val value
     *
     * @return string error message
     */
    public function setParamU($key, $val)
    {
        return $this->param->Set($key, $val, \Anakeen\Core\Internal\Param::PARAM_USER . $this->user->id, $this->id);
    }

    /**
     * declare new \application parameter
     *
     * @param string $key
     * @param array  $val
     */
    public function setParamDef($key, $val)
    {
        // add new \param definition
        $pdef = \Anakeen\Core\Internal\ParamDef::getParamDef($key, $this->id);

        $oldValues = array();
        if (!$pdef) {
            $pdef = new \Anakeen\Core\Internal\ParamDef($this->dbaccess);
            $pdef->name = $key;
            $pdef->isuser = "N";
            $pdef->isstyle = "N";
            $pdef->isglob = "N";
            $pdef->appid = $this->id;
            $pdef->descr = "";
            $pdef->kind = "text";
        } else {
            $oldValues = $pdef->getValues();
        }

        if (is_array($val)) {
            if (isset($val["kind"])) {
                $pdef->kind = $val["kind"];
            }
            if (isset($val["user"]) && $val["user"] == "Y") {
                $pdef->isuser = "Y";
            } else {
                $pdef->isuser = "N";
            }
            if (isset($val["style"]) && $val["style"] == "Y") {
                $pdef->isstyle = "Y";
            } else {
                $pdef->isstyle = "N";
            }
            if (isset($val["descr"])) {
                $pdef->descr = $val["descr"];
            }
            if (isset($val["global"]) && $val["global"] == "Y") {
                $pdef->isglob = "Y";
            } else {
                $pdef->isglob = "N";
            }
        }

        if ($pdef->appid == $this->id) {
            if ($pdef->isAffected()) {
                $pdef->Modify();
                // migrate paramv values in case of type changes
                $newValues = $pdef->getValues();
                if ($oldValues['isglob'] != $newValues['isglob']) {
                    $ptype = $oldValues['isglob'] == 'Y' ? \Anakeen\Core\Internal\Param::PARAM_GLB : \Anakeen\Core\Internal\Param::PARAM_APP;
                    $ptypeNew = $newValues['isglob'] == 'Y' ? \Anakeen\Core\Internal\Param::PARAM_GLB : \Anakeen\Core\Internal\Param::PARAM_APP;
                    $pv = new \Anakeen\Core\Internal\Param($this->dbaccess, array(
                        $pdef->name,
                        $ptype,
                        $pdef->appid
                    ));
                    if ($pv->isAffected()) {
                        $pv->set($pv->name, $pv->val, $ptypeNew, $pv->appid);
                    }
                }
            } else {
                $pdef->add();
            }
        }
    }

    /**
     * Add temporary parameter to ths application
     * Can be use to transmit global variable or to affect Layout
     *
     * @param string $key
     * @param string $val
     */
    public function setVolatileParam($key, $val)
    {
        if ($this->hasParent()) {
            $this->parent->setVolatileParam($key, $val);
        } else {
            $this->param->SetVolatile($key, $val);
        }
    }

    /**
     * get parameter value
     *
     * @param string $key
     * @param string $default value if not set
     *
     * @return string
     */
    public function getParam($key, $default = "")
    {
        if (!isset($this->param)) {
            return ($default);
        }
        $z = $this->param->Get($key, "z");

        if ($z === "z") {
            if ($this->hasParent()) {
                return $this->parent->GetParam($key, $default);
            }
        } else {
            return ($z);
        }
        return ($default);
    }

    /**
     * create/update application parameter definition
     *
     * @param array $tparam all parameter definition
     * @param bool  $update
     */
    public function initAllParam($tparam, $update = false)
    {
        if (is_array($tparam)) {
            reset($tparam);
            foreach ($tparam as $k => $v) {
                $this->SetParamDef($k, $v); // update definition
                if ($update) {
                    // don't modify old parameters
                    if ($this->param && $this->param->Get($k, null) === null) {
                        // set only new \parameters or static variable like VERSION
                        $this->SetParam($k, $v);
                    }
                } else {
                    $this->SetParam($k, $v);
                }
            }
        }
    }

    /**
     * get all parameters values indexed by name
     *
     * @return array all paramters values
     */
    public function getAllParam()
    {
        $list = $this->param->buffer;
        if ($this->hasParent()) {
            $list2 = $this->parent->GetAllParam();
            $list = array_merge($this->param->buffer, $list2);
        }

        return ($list);
    }








    /**
     * delete application
     * database application reference are destroyed but application files are not removed from server
     *
     * @return string
     */
    public function deleteApp()
    {
        // delete acl
        $acl = new \Acl($this->dbaccess);
        $acl->DelAppAcl($this->id);
        // delete actions
        LogManager::debug("Delete {$this->name}");
        $query = new \Anakeen\Core\Internal\QueryDb("", Action::class);
        $query->basic_elem->sup_where = array(
            "id_application = {$this->id}"
        );
        $list = $query->Query();

        if ($query->nb > 0) {
            /**
             * @var \Anakeen\Core\Internal\Action $v
             */
            foreach ($list as $v) {
                LogManager::debug(" Delete action {$v->name} ");
                $err = $v->Delete();
                if ($err != '') {
                    return $err;
                }
            }
        }
        unset($query);

        unset($list);
        // delete params
        $param = new \Anakeen\Core\Internal\Param($this->dbaccess);
        $param->DelAll($this->id);
        // delete application
        $err = $this->Delete();
        return $err;
    }

    /**
     * translate text
     * use gettext catalog
     *
     * @param string $code text to translate
     *
     * @return string
     */
    public static function text($code)
    {
        if ($code == "") {
            return "";
        }
        return _($code);
    }

    /**
     * Write default ACL when new \user is created
     *
     * @TODO not used - to remove
     *
     * @param int $iduser
     *
     * @throws \Dcp\Db\Exception
     */
    public function updateUserAcl($iduser)
    {
        $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, self::class);
        $query->AddQuery("available = 'Y'");
        $allapp = $query->Query();
        $acl = new \Acl($this->dbaccess);

        foreach ($allapp as $v) {
            $permission = new \Permission($this->dbaccess);
            $permission->id_user = $iduser;
            $permission->id_application = $v->id;

            $privileges = $acl->getDefaultAcls($v->id);

            foreach ($privileges as $aclid) {
                $permission->id_acl = $aclid;
                if (($permission->id_acl > 0) && (!$permission->Exists($permission->id_user, $v->id))) {
                    $permission->add();
                }
            }
        }
    }

    /**
     * return id from name for an application
     *
     * @param string $name
     *
     * @return int (0 if not found)
     */
    public function getIdFromName($name)
    {
        $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, self::class);
        $query->AddQuery("name = '" . pg_escape_string(trim($name)) . "'");
        $app = $query->Query(0, 0, "TABLE");
        if (is_array($app) && isset($app[0]) && isset($app[0]["id"])) {
            return $app[0]["id"];
        }
        return 0;
    }

    /**
     * verify if application object has parent application
     *
     * @return bool
     */
    public function hasParent()
    {
        return (is_object($this->parent) && ($this->parent !== $this));
    }

    /**
     * Initialize ACLs with group_default='Y'
     */
    private function _initACLWithGroupDefault()
    {
        $res = array();
        try {
            DbManager::query(sprintf("SELECT * FROM acl WHERE id_application = %s AND group_default = 'Y'", $this->id), $res, false, false);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        foreach ($res as $acl) {
            $permission = new \Permission($this->dbaccess);
            if ($permission->Exists(\Anakeen\Core\Account::GALL_ID, $this->id, $acl['id'])) {
                continue;
            }
            $permission->Affect(array(
                'id_user' => \Anakeen\Core\Account::GALL_ID,
                'id_application' => $this->id,
                'id_acl' => $acl['id']
            ));
            $err = $permission->add();
            if ($err != '') {
                return $err;
            }
        }
        return '';
    }
}
