<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package CONTROL
*/

require_once 'class/Class.WIFF.php';

class Logger
{
    const defaultFacility = LOG_USER;
    private $ident = null;
    private $facility = null;
    private $logfile = null;
    private static $priNames = null;
    private static $facilityNames = null;
    public function __construct($ident)
    {
        $this->ident = $ident;
        $this->facility = self::defaultFacility;
        $this->initPriNames();
        $this->initFacilityNames();
    }
    public function setSyslogFacility($facility = null)
    {
        if ($facility === null) {
            $facility = self::defaultFacility;
        }
        foreach (self::$facilityNames as $facilityCode => $facilityName) {
            if ($facility == (string)$facilityCode || $facility == $facilityName) {
                $this->facility = $facilityCode;
                return true;
            }
        }
        $this->log(LOG_INFO, sprintf("Invalid syslog-facility '%s'. Using default facility.", $facility));
        return false;
    }
    public function setLogFile($logfile = null)
    {
        if (!file_exists($logfile)) {
            $dirname = dirname($logfile);
            if (!is_dir($dirname)) {
                mkdir($dirname, 0777, true);
            }
        }
        $this->logfile = $logfile;
    }
    private function initPriNames()
    {
        if (self::$priNames !== null) {
            return;
        }
        self::$priNames = array(
            LOG_EMERG => 'LOG_EMERG',
            LOG_ALERT => 'LOG_ALERT',
            LOG_CRIT => 'LOG_CRIT',
            LOG_ERR => 'LOG_ERR',
            LOG_WARNING => 'LOG_WARNING',
            LOG_NOTICE => 'LOG_NOTICE',
            LOG_INFO => 'LOG_INFO',
            LOG_DEBUG => 'LOG_DEBUG'
        );
    }
    private function initFacilityNames()
    {
        if (self::$facilityNames !== null) {
            return;
        }
        self::$facilityNames = array(
            LOG_AUTH => 'LOG_AUTH',
            LOG_AUTHPRIV => 'LOG_AUTHPRIV',
            LOG_CRON => 'LOG_CRON',
            LOG_DAEMON => 'LOG_DAEMON',
            LOG_KERN => 'LOG_KERN',
            LOG_LOCAL0 => 'LOG_LOCAL0',
            LOG_LOCAL1 => 'LOG_LOCAL1',
            LOG_LOCAL2 => 'LOG_LOCAL2',
            LOG_LOCAL3 => 'LOG_LOCAL3',
            LOG_LOCAL4 => 'LOG_LOCAL4',
            LOG_LOCAL5 => 'LOG_LOCAL5',
            LOG_LOCAL6 => 'LOG_LOCAL6',
            LOG_LOCAL7 => 'LOG_LOCAL7',
            LOG_LPR => 'LOG_LPR',
            LOG_MAIL => 'LOG_MAIL',
            LOG_NEWS => 'LOG_NEWS',
            LOG_SYSLOG => 'LOG_SYSLOG',
            LOG_USER => 'LOG_USER',
            LOG_UUCP => 'LOG_UUCP'
        );
    }
    public function log($pri, $msg)
    {
        $msg = sprintf("%s %s %s", PHP_SAPI, $this->priName($pri), $msg);
        /*
         * Send to local logfile
        */
        if (isset($this->logfile)) {
            $newLine = sprintf("%s dynacase-control[%s]: %s", date("c") , getmypid() , $msg);
            if (substr($newLine, -1, 1) != "\n") {
                $newLine.= "\n";
            }
            error_log($newLine, 3, $this->logfile);
        }
        /*
         * Send to syslog
        */
        openlog($this->ident, LOG_PID, $this->facility);
        syslog($pri, $msg);
    }
    private function priName($pri)
    {
        return (isset(self::$priNames[$pri]) ? self::$priNames[$pri] : 'LOG_UNKNOWN');
    }
}
