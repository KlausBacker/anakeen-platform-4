<?php

namespace Anakeen\Core\Internal;

/**
 * Log manager
 * log message according to CORE_LOGLEVEL parameter
 * @class Log
 *
 */
class Log
{
    public $loghead;
    public $application;
    public $function;
    private $deb;
    private $fin;
    private $tic;
    private $ptext;
    /**
     * @var string Level to log
     */
    protected $logLevel = null;
    /**
     * Constant to set log to debug level
     * Debug level is used by Core.
     * It's used to assert taht Core works properly
     */
    const DEBUG = "D";
    /**
     * Constant to set log to callstack level
     */
    const CALLSTACK = "C";
    /**
     * Constant to set log to trace level
     * The trace level is a level reserved for user usage.
     * Core will never log with this level
     */
    const TRACE = "T";
    /**
     * Constant to set log to info level
     */
    const INFO = "I";
    /**
     * Constant to set log to warning level
     */
    const WARNING = "W";
    /**
     * Constant to set log to error level
     */
    const ERROR = "E";
    /**
     * Constant to set log to fatal level
     */
    const FATAL = "F";
    /**
     * Constant to set log to deprecated level
     */
    const DEPRECATED = "O";
    // ------------------------------------------------------------------------
    
    /**
     * @api initialize log manager
     * @param string $logfile
     * @param string $application
     * @param string $function
     */
    public function __construct($logfile = "", $application = "", $function = "")
    {
        $this->usesyslog = 0;
        if ($logfile == "") {
            $this->usesyslog = 1;
        } else {
            $fd = fopen($logfile, "a");
            if (!$fd) {
                $this->usesyslog = 1;
            } else {
                $this->logfile = $logfile;
                fclose($fd);
            }
        }
        $this->application = $application;
        $this->function = $function;
    }







    /**
     * @param string $logLevel
     */
    public function setLogLevel($logLevel)
    {
        $this->logLevel = $logLevel;
    }
    /**
     * @return string
     */
    public function getLogLevel()
    {
        if ($this->logLevel === null) {
            $this->logLevel = \Anakeen\Core\ContextManager::getParameterValue("CORE_LOGLEVEL", "IWEF");
        }
        return $this->logLevel;
    }

    

    /**
     * main log function
     * @param string $sta log code (one character : IWEFDOT)
     * @param string $str message to log
     * @param null $args unused
     * @param int $facility syslog level
     */
    public function wlog($sta, $str, $args = null, $facility = LOG_LOCAL6)
    {
        global $_SERVER;
        
        if (!$str) {
            return;
        }
        if (is_array($str)) {
            $str = implode(", ", $str);
        }
        if ($sta == "S" || (is_int(strpos($this->getLogLevel(), $sta)))) {
            $addr = isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : '';
            $appf = "[{$sta}] Dynacase";
            $appf.= ($this->application != "" ? ":" . $this->application : "");
            $appf.= ($this->function != "" ? ":" . $this->function : "");
            $str = ' ' . $this->loghead . ': ' . $str;
            if (!$this->usesyslog) {
                $xx = date("d/m/Y H:i:s", time()) . " {$appf} [{$addr}] ";
                $xx = $xx . $str . "\n";
                $fd = fopen($this->logfile, "a");
                fputs($fd, $xx);
                fclose($fd);
            } else {
                switch ($sta) {
                    case self::DEBUG:
                        $pri = LOG_DEBUG;
                        break;

                    case self::DEPRECATED:
                        $td = @debug_backtrace(false);
                        $class = (isset($td[4]["class"])) ? $td[4]["class"] : '';
                        if ($str) {
                            $str.= ' ';
                        }
                        $str.= sprintf("%s called in %s%s%s(), file %s:%s", isset($td[3]["function"]) ? $td[3]["function"] : '', $class, $class ? '::' : '', isset($td[4]["function"]) ? $td[4]["function"] : '', isset($td[3]["file"]) ? $td[3]["file"] : '', isset($td[3]["line"]) ? $td[3]["line"] : '');
                        $pri = LOG_INFO;
                        break;

                    case self::INFO:
                        $pri = LOG_INFO;
                        break;

                    case self::WARNING:
                        $pri = LOG_WARNING;
                        break;

                    case self::ERROR:
                        $pri = LOG_ERR;
                        break;

                    case self::FATAL:
                        $pri = LOG_ALERT;
                        break;

                    case self::TRACE:
                        $pri = LOG_DEBUG;
                        break;

                    default:
                        $pri = LOG_NOTICE;
                }
                if (empty($_SERVER['HTTP_HOST'])) {
                    error_log(sprintf("%s self::$appf %s", date("d/m/Y H:i:s", time()), $str));
                }
                openlog("{$appf}", 0, $facility);
                syslog($pri, "[{$addr}] " . $str);
                closelog();
                
                if ($sta == "E") {
                    error_log($str); // use apache syslog also
                }
            }
        }
    }
} // Class.Log
