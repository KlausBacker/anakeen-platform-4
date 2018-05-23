<?php
/*
 * @author Anakeen
 * @package FDL
*/

class SessionUtils
{
    private $dbaccess;

    public function __construct($dbaccess)
    {
        $this->dbaccess = $dbaccess;
    }

    public function getSessionMaxAge()
    {
        \Anakeen\Core\DbManager::query("SELECT val FROM paramv WHERE name = 'CORE_SESSIONMAXAGE'", $seconds, true, true);

        if ($seconds === false) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "exec_query returned an empty result set");
            return false;
        }
        if (is_numeric($seconds)) {
            return $seconds . " seconds";
        }
        return $seconds;
    }

    public function getSessionMaxAgeSeconds()
    {
        $session_maxage = $this->getSessionMaxAge();
        if ($session_maxage === false) {
            return false;
        }
        if (preg_match('/^(\d+)\s+(\w+)/i', $session_maxage, $m)) {
            $maxage = $m[1];
            $unit = strtolower($m[2]);
            switch (substr($unit, 0, 1)) {
                case 'y':
                    $maxage = $maxage * 364 * 24 * 60 * 60;
                    break; # years

                case 'm':
                    if (substr($unit, 0, 2) == 'mo') {
                        $maxage = $maxage * 30 * 24 * 60 * 60;
                        break; # months
                    } else {
                        $maxage = $maxage * 60;
                        break; # minutes
                    }
                // no break
                case 'w':
                    $maxage = $maxage * 7 * 24 * 60 * 60;
                    break; # weeks

                case 'd':
                    $maxage = $maxage * 24 * 60 * 60;
                    break; # days

                case 'h':
                    $maxage = $maxage * 60 * 60;
                    break; # hours

                case 's':
                    break; # seconds

                default:
                    return false;
            }
            return $maxage;
        }
        return false;
    }

    public function deleteExpiredSessionFiles()
    {
        $session_maxage = $this->getSessionMaxAgeSeconds();
        if ($session_maxage === false) {
            $err = sprintf("Malformed CORE_SESSIONMAXAGE");
            return $err;
        }
        $maxage = time() - $session_maxage;

        $sessionDir = sprintf("%s/var/session", DEFAULT_PUBDIR);
        $dir = opendir($sessionDir);
        if ($dir === false) {
            $err = sprintf("Error opening directory '%s'.", $sessionDir);
            return $err;
        }

        while ($file = readdir($dir)) {
            if (preg_match("/^sess_(.+)$/", $file, $m)) {
                $sess_file = sprintf("%s/%s", $sessionDir, $file);
                $stat = @stat($sess_file);
                if ($stat !== false && $stat['mtime'] < $maxage) {
                    @unlink($sess_file);
                }
            }
        }
        closedir($dir);

        return "";
    }
}
