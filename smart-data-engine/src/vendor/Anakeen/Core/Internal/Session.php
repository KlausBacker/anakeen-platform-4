<?php

namespace Anakeen\Core\Internal;

use Anakeen\Core\ContextManager;
use Anakeen\Core\Settings;
use Anakeen\Core\Utils\Date;
use Anakeen\LogManager;
use Slim\Http\Cookies;

class Session extends DbObj
{
    const SESSION_CT_CLOSE = 2;
    const SESSION_CT_ARGS = 3;
    const SESSION_MIN_BYTE_LENGTH = 16; /* 16 bytes = 128 bits */
    const SESSION_SUBDIR='var/session';
    public $fields = array(
        "id",
        "userid",
        "name",
        "last_seen"
    );

    public $id_fields = array(
        "id"
    );
    public $id;
    public $userid;
    public $name;
    public $last_seen;
    public $status;
    private $sendCookie = true;
    public $dbtable = "sessions";

    public $sqlcreate = "create table sessions ( id text,
                        userid   int,
                        name text not null,
                        last_seen timestamp not null DEFAULT now() );
                  create unique index sessions_idx on sessions(id);
                  create index sessions_idx_userid on sessions(userid);";

    public $sessiondb;

    const PARAMNAME = 'anksession';
    protected static $session_name = self::PARAMNAME;

    public function __construct()
    {
        if (!empty($_SERVER['HTTP_HOST'])) {
            /** @noinspection PhpIncludeInspection */
            include_once(DEFAULT_PUBDIR . "/config/sessionHandler.php");
        }
        parent::__construct();

        $this->last_seen = strftime('%Y-%m-%d %H:%M:%S', time());
    }

    public function useCookie(bool $useIt)
    {
        $this->sendCookie = $useIt;
    }

    public static function getName()
    {
        return self::$session_name . '-' . $_SERVER["SERVER_PORT"];
    }

    public function set($id = "")
    {
        global $_SERVER;

        if (!$this->sessionDirExistsAndIsWritable()) {
            return false;
        }

        $this->gcSessions();
        $createNewSession = true;

        if ($id) {
            $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, \Anakeen\Core\Internal\Session::class);
            $query->addQuery("id = '" . pg_escape_string($id) . "'");
            $list = $query->Query(0, 0, "TABLE");
            if ($query->nb != 0) {
                $this->Affect($list[0]);
                if (!$this->hasExpired()) {
                    $createNewSession = false;
                    $this->touch();
                    session_name(self::getName());
                    session_id($id);
                    @session_start();
                    @session_write_close(); // avoid block
                }
            }
        }

        if ($createNewSession) {
            $u = new \Anakeen\Core\Account();
            if ((!empty($_SERVER['PHP_AUTH_USER'])) && $u->SetLoginName($_SERVER['PHP_AUTH_USER'])) {
                $this->open($u->id);
            } else {
                $this->open(\Anakeen\Core\Account::ANONYMOUS_ID); //anonymous session
            }
        }

        // set cookie session
        if (!empty($_SERVER['HTTP_HOST'])) {
            $this->setCookieSession($this->id, $this->SetTTL());
        }
        return true;
    }

    public static function getWebRootPath()
    {
        if (!isset($_SERVER['SCRIPT_FILENAME'])) {
            return false;
        }
        if (!isset($_SERVER['SCRIPT_NAME'])) {
            return false;
        }
        /*
         * Get absolute context's pathname (with trailing '/'):
         *
         *     "/var/www/foo/"
         *
        */
        $contextRoot = realpath(PUBLIC_DIR);
        if ($contextRoot === false) {
            return false;
        }
        $contextRoot .= '/';
        /*
         *  Get absolute script's filename:
         *
         *     "/var/www/foo/bar/baz.php"
         *s
        */
        $scriptFilename = $_SERVER['SCRIPT_FILENAME'];
        /*
         * Remove leading context's pathname from script's filename:
         *
         *     "/var/www/foo/bar/baz.php" - "^/var/www/foo/" => "bar/baz.php"
         *
         * This gives us the script's filename relative to the context's root.
        */

        $pos = strpos($scriptFilename, $contextRoot);
        if ($pos !== 0) {
            return false;
        }

        $relativeScriptFilename = substr($scriptFilename, strlen($contextRoot));
        /*
         * Remove trailing relative script's filename from script's name by finding the
         * relative script's filename by the right :
         *
         *     "/x/y/z/bar/baz.php" - "bar/baz.php$" => "/x/y/z/"
         *
         * This gives us the Web root directory.
        */
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $pos = strrpos($scriptName, $relativeScriptFilename);
        $webRootLen = (strlen($scriptName) - strlen($relativeScriptFilename));
        if ($pos !== $webRootLen) {
            return false;
        }

        $webRoot = substr($scriptName, 0, $webRootLen);

        return $webRoot;
    }

    public function setCookieSession($id, $ttl = 0)
    {
        $this->setcookie($this->name, $id, $ttl, '/', null, null, true);
    }

    /**
     * Closes session and removes all datas
     */
    public function close()
    {
        global $_SERVER; // use only cache with HTTP
        if (!empty($_SERVER['HTTP_HOST'])) {
            session_name($this->name);
            session_id($this->id);
            @session_start();
            @session_destroy();
            // delete session cookie
            $this->setcookie($this->name, false, time() - 3600, null, null, null, true);
            $this->Delete();
        }
        $this->status = self::SESSION_CT_CLOSE;
        return $this->status;
    }

    /**
     * Closes all session
     *
     * @param int|null $uid if set close specific user session
     *
     * @return int
     */
    public function closeAll($uid = null)
    {
        if ($uid === null) {
            $this->query(sprintf("delete from sessions where name = '%s';", pg_escape_string(self::getName())));
        } else {
            $this->query(sprintf("delete from sessions where name = '%s' and userid=%d;", pg_escape_string(self::getName()), $uid));
        }
        $this->status = self::SESSION_CT_CLOSE;
        return $this->status;
    }

    /**
     * Closes all user's sessions
     *
     * @param int $uid if set close specific user session
     *
     * @return int|string
     */
    public function closeUsers($uid = -1)
    {
        if (!$uid > 0) {
            return '';
        }
        $this->query("delete from sessions where userid= '" . pg_escape_string($uid) . "'");
        $this->status = self::SESSION_CT_CLOSE;
        return $this->status;
    }

    public function open($uid = \Anakeen\Core\Account::ANONYMOUS_ID)
    {
        $idsess = $this->newId();
        global $_SERVER; // use only cache with HTTP
        if (!empty($_SERVER['HTTP_HOST'])) {
            session_name(self::getName());
            session_id($idsess);
            @session_start();
            @session_write_close(); // avoid block
            //  $this->initCache();
        }
        $this->name = self::getName();
        $this->id = $idsess;
        $this->userid = $uid;
        $this->last_seen = strftime('%Y-%m-%d %H:%M:%S', time());
        $this->add();
    }
    // --------------------------------
    // Stocke une variable de session args
    // $v est une chaine !
    // --------------------------------
    public function register($k = "", $v = "")
    {
        if ($k == "") {
            $this->status = self::SESSION_CT_ARGS;
            return $this->status;
        }
        global $_SERVER; // use only cache with HTTP
        if (!empty($_SERVER['HTTP_HOST']) && $this->name) {
            session_name($this->name);
            session_id($this->id);
            @session_start();
            $_SESSION[$k] = $v;
            @session_write_close(); // avoid block
        }

        return true;
    }
    // --------------------------------
    // Récupère une variable de session
    // $v est une chaine !
    // --------------------------------
    public function read($k = "", $d = "")
    {
        if (empty($_SERVER['HTTP_HOST']) || !$this->name) {
            return ($d);
        }
        /* Load session's data only once as requested by #4825 */
        $sessionOpened = false;
        if (empty($_SESSION)) {
            session_name($this->name);
            session_id($this->id);
            session_start();

            $sessionOpened = true;
        }
        if (isset($_SESSION[$k])) {
            $val = $_SESSION[$k];
        } else {
            $val = $d;
        }
        if ($sessionOpened) {
            @session_write_close();
        }
        return $val;
    }
    // --------------------------------
    // Détruit une variable de session
    // $v est une chaine !
    // --------------------------------
    public function unregister($k = "")
    {
        global $_SERVER; // use only cache with HTTP
        if ($this->name && !empty($_SERVER['HTTP_HOST'])) {
            session_name($this->name);
            session_id($this->id);
            @session_start();
            unset($_SESSION[$k]);
            @session_write_close(); // avoid block
        }
        return true;
    }

    /**
     * Get, or generate, a "cache busting" key
     *
     * @param string $prefix
     * @return string
     */
    public function getUKey($prefix = '')
    {
        $uKey = $this->read('_uKey_', false);
        if ($uKey === false) {
            $uKey = uniqid($prefix);
            $this->register('_uKey_', $uKey);
        }
        return $uKey;
    }
    // ------------------------------------------------------------------------
    // utilities functions (private)
    // ------------------------------------------------------------------------
    public function newId()
    {
        $byteLength = (int)\Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, 'CORE_SESSION_BYTE_LENGTH');
        if ($byteLength < self::SESSION_MIN_BYTE_LENGTH) {
            $byteLength = self::SESSION_MIN_BYTE_LENGTH;
        }
        return self::randomId($byteLength);
    }

    /**
     * Get a new cryptographically strong random id
     *
     * Throws an exception if no cryptographically strong random bytes could be
     * obtained from openssl: this might occurs on broken or old system.
     *
     * @param int $byteLength The number of bytes to get from the CSPRNG
     * @return string The random bytes in hexadecimal representation (e.g. "a7d1f43b")
     * @throws \Anakeen\Exception
     */
    private static function randomId($byteLength)
    {
        $strong = false;
        $bytes = openssl_random_pseudo_bytes($byteLength, $strong);
        if ($bytes === false || $strong === false) {
            throw new \Anakeen\Exception(sprintf("Unable to get cryptographically strong random bytes from openssl: your system might be broken or too old."));
        }
        return bin2hex($bytes);
    }

    /**
     * replace value of global parameter in session cache
     * @param string $paramName
     * @param string $paramValue
     * @return bool
     */
    public function replaceGlobalParam($paramName, $paramValue)
    {
        global $_SERVER; // use only cache with HTTP
        if (!empty($_SERVER['HTTP_HOST'])) {
            session_name($this->name);
            session_id($this->id);
            @session_start();
            foreach ($_SESSION as $k => $v) {
                if (preg_match("/^sessparam[0-9]+$/", $k)) {
                    if (isset($v[$paramName])) {
                        $_SESSION[$k][$paramName] = $paramValue;
                    }
                }
            }
            @session_write_close(); // avoid block
        }
        return true;
    }

    public function setTTL()
    {
        $ttliv = $this->getSessionTTL(0);
        if ($ttliv > 0) {
            //$ttli->CloseConnect();
            return (time() + $ttliv);
        }
        return 0;
    }

    public function getSessionTTL($default = 0, $ttlParamName = '')
    {
        if ($ttlParamName == '') {
            if ($this->userid == \Anakeen\Core\Account::ANONYMOUS_ID) {
                $ttlParamName = 'CORE_GUEST_SESSIONTTL';
            } else {
                $ttlParamName = 'CORE_SESSIONTTL';
            }
        }
        return intval(\Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, $ttlParamName, $default));
    }

    public function getSessionGcProbability($default = "0.01")
    {
        return \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "CORE_SESSIONGCPROBABILITY", $default);
    }

    public function touch()
    {
        $this->last_seen = strftime('%Y-%m-%d %H:%M:%S', time());
        $err = $this->modify();
        return $err;
    }

    public function deleteUserExpiredSessions()
    {
        $ttl = $this->getSessionTTL(0, 'CORE_SESSIONTTL');
        if ($ttl > 0) {
            return $this->query(sprintf(
                "DELETE FROM sessions WHERE userid != %s AND last_seen < timestamp 'now()' - interval '%s seconds'",
                \Anakeen\Core\Account::ANONYMOUS_ID,
                pg_escape_string($ttl)
            ));
        }
        return '';
    }

    public function deleteGuestExpiredSessions()
    {
        $ttl = $this->getSessionTTL(0, 'CORE_GUEST_SESSIONTTL');
        if ($ttl > 0) {
            return $this->query(sprintf(
                "DELETE FROM sessions WHERE userid = %s AND last_seen < timestamp 'now()' - interval '%s seconds'",
                \Anakeen\Core\Account::ANONYMOUS_ID,
                pg_escape_string($ttl)
            ));
        }
        return '';
    }

    public function deleteMaxAgedSessions()
    {
        $maxage = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, 'CORE_SESSIONMAXAGE', '');
        if ($maxage != '') {
            return $this->query(sprintf("DELETE FROM sessions WHERE last_seen < timestamp 'now()' - interval '%s'", pg_escape_string($maxage)));
        }
        return '';
    }

    public function gcSessions()
    {
        $gcP = $this->getSessionGcProbability();
        if ($gcP <= 0) {
            return "";
        }
        $p = rand() / getrandmax();
        if ($p <= $gcP) {
            $err = $this->deleteUserExpiredSessions();
            if ($err != "") {
                error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Error cleaning up user sessions: " . $err);
            }
            $err = $this->deleteGuestExpiredSessions();
            if ($err != "") {
                error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Error cleaning up guest sessions: " . $err);
            }
            $err = $this->deleteMaxAgedSessions();
            if ($err != "") {
                error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Error cleaning up max-aged sessions: " . $err);
            }
        }
        return "";
    }

    public function setuid($uid)
    {
        if (!is_int($uid)) {
            $u = new \Anakeen\Core\Account();
            if ($u->SetLoginName($uid)) {
                $uid = $u->id;
            } else {
                $err = "Could not resolve login name '" . $uid . "' to uid";
                error_log(__CLASS__ . "::" . __FUNCTION__ . " " . $err);
                return $err;
            }
        }
        if ($this->userid != $uid) {
            if (isset($_SESSION)) {
                $sessionCopy = $_SESSION;
                // Reset session id when user id does not match regitered session user id
                $this->close();
                $this->set();
                session_id($this->id);
                session_start();
                // Copy session values from old to new session
                foreach ($sessionCopy as $k => $v) {
                    $_SESSION[$k] = $v;
                }
                session_write_close(); // avoid block
            }
        }

        $this->userid = $uid;
        return $this->modify();
    }

    public function sessionDirExistsAndIsWritable()
    {
        $sessionDir = sprintf("%s/%s", DEFAULT_PUBDIR, self::SESSION_SUBDIR);
        if (!is_dir($sessionDir)) {
            trigger_error(sprintf("Session directory '%s' does not exists.", $sessionDir));
            return false;
        }

        if (!is_writable($sessionDir)) {
            trigger_error(sprintf("Session directory '%s' is not writable.", $sessionDir));
            return false;
        }
        return true;
    }

    public function hasExpired()
    {
        $ttl = $this->getSessionTTL(0);
        if ($ttl > 0) {
            $now = time();
            $last_seen = Date::stringDateToUnixTs($this->last_seen);
            if ($now > $last_seen + $ttl) {
                return true;
            }
        }
        return false;
    }

    public function removeSessionFile($sessid = null)
    {
        if ($sessid === null) {
            $sessid = $this->id;
        }
        $sessionFile = sprintf("%s/%s/sess_%s", DEFAULT_PUBDIR, self::SESSION_SUBDIR, $sessid);
        if (file_exists($sessionFile)) {
            unlink($sessionFile);
        }
    }

    /**
     * Delete all user's sessions except the current session.
     *
     * @param string $userId          The user id (default is $this->userid)
     * @param string $exceptSessionId The session id to keep (default is $this->id)
     * @return string empty string on success, or the SQL error message
     */
    public function deleteUserSessionsExcept($userId = '', $exceptSessionId = '')
    {
        if ($userId == '') {
            $userId = $this->userid;
        }
        if ($exceptSessionId == '') {
            $exceptSessionId = $this->id;
        }
        return $this->query(sprintf("DELETE FROM sessions WHERE userid = %d AND id != '%s'", $userId, pg_escape_string($exceptSessionId)));
    }

    private function setcookie($name, $value = null, $expire = null, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        if ($this->sendCookie) {
            if ($path === null) {
                $webRootPath = self::getWebRootPath();
                if ($webRootPath !== false) {
                    $path = preg_replace(':/+:', '/', $webRootPath);
                }
            }
            $cookie=new Cookies([]);

            $config=[
                "value" =>$value,
                "expires"=>$expire==0?null:$expire,
                "path"=>$path,
                "domain"=>$domain,
                "secure"=>$secure,
                "httponly"=>$httponly,
                "samesite"=>"strict"
            ];

            $opts=ContextManager::getParameterValue(Settings::NsSde, "CORE_SESSION_COOKIE");
            if ($opts) {
                $cookieOptions=json_decode($opts, true);
                if (! is_array($cookieOptions)) {
                    LogManager::error(sprintf("CORE_SESSION_COOKIE is not a json array : \"%s\"", $opts));
                }
                $allowedOptions=["path","expires", "secure", "hostonly", "httponly", "samesite"];
                foreach ($cookieOptions as $k => $v) {
                    if (in_array($k, $allowedOptions)===false) {
                        LogManager::error(sprintf("CORE_SESSION_COOKIE key \"%s\" not supported. Allowed keys are %s", $k, implode(", ", $allowedOptions)));
                    }
                    $config[$k]=$v;
                }
            }

            $cookie->set($name, $config);
            $headers=$cookie->toHeaders();
            foreach ($headers as $header) {
                header(sprintf("set-cookie: %s", $header));
            }
            return true;
        }
        return false;
    }
} // Class Session
