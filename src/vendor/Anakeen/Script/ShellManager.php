<?php

namespace Anakeen\Script;

use Anakeen\Core\Utils\Gettext;

class ShellManager
{
    const SCRIPTDIR = "API";
    protected static $opts = [];
    protected static $programName = "ank.php";

    public static function recordArgs(array $argv, $programeName = null)
    {
        if ($programeName) {
            self::$programName = $programeName;
        }
        self::$opts = [];
        foreach ($argv as $k => $v) {
            if (preg_match("/--([^=]+)=(.*)/", $v, $reg)) {
                if (substr($reg[1], -2) === "[]") {
                    self::$opts[substr($reg[1], 0, -2)][] = $reg[2];
                } else {
                    self::$opts[$reg[1]] = $reg[2];
                }
            } elseif (preg_match("/--(.+)/", $v, $reg)) {
                self::$opts[$reg[1]] = true;
            }
        }

        foreach (self::$opts as $k => $opt) {
            // Record as global for Old functions
            // @TODO another way to propagate
            $_GET[$k] = $opt;
        }
    }

    public static function getArg($argName)
    {
        if (isset(self::$opts[$argName])) {
            return self::$opts[$argName];
        }
        return null;
    }


    public static function getArgs()
    {
        return self::$opts;
    }

    /**
     * @return string
     */
    public static function getUsage()
    {
        $lines[] = sprintf("Usage:");
        /** @noinspection HtmlUnknownAttribute */
        $lines[] = sprintf("--script=<script file>");
        $lines[] = sprintf("\t--list\t\t\tList all available scripts");
        $lines[] = sprintf("\t--login\t\t\tExecute as special account (admin is default)");
        /** @noinspection HtmlUnknownAttribute */
        $lines[] = sprintf("--route=<route id>");
        $lines[] = sprintf("\t--list\t\t\tList all recorded routes");
        $lines[] = sprintf("\t--method=<HTTP Method>\t[GET|POST|PUT|DELETE] (default GET)");
        $lines[] = sprintf("\t--arg-<x>=<value>\tRoute argument <x> value");
        /** @noinspection RequiredAttributes */
        $lines[] = sprintf("\t--content=<data file>\tFor POST/PUT methods");
        $lines[] = sprintf("\t--query=<optional args>\tLike \"a=1&b=2\"");
        $lines[] = sprintf("--system");
        $lines[] = sprintf("\t--start\t\t\tReset Cache and Enable Http access");
        $lines[] = sprintf("\t--stop\t\t\tDisable Http access");
        $lines[] = sprintf("\t--verbose\t\tVerbose mode");
        $lines[] = sprintf("\t--unstop\t\tEnable Http access");
        $lines[] = sprintf("\t--resetRouteConfig\tReload route configuration");
        $lines[] = sprintf("\t--upgradeVersion\tReset WVERSION");
        $lines[] = sprintf("\t--localeGen\t\tGenerate locale catalog");
        $lines[] = sprintf("\t--style\t\t\tReset Css generation");
        $lines[] = sprintf("\t--clearFile\t\tDelete file cache");
        $lines[] = sprintf("--help\t\t\t\tThis usage");


        return self::$programName . "\n" . implode("\n\t", $lines) . "\n";
    }

    public static function runScript($scriptName)
    {
        $apifile = trim(sprintf("%s/%s/%s.php", DEFAULT_PUBDIR, self::SCRIPTDIR, $scriptName));


        if (!file_exists($apifile)) {
            throw new Exception(sprintf(Gettext::___("API file \"%s\" not found", "core"), $apifile));
        } else {
            self::initContext();
            try {
                require($apifile);
            } catch (UsageException $e) {
                switch ($e->getDcpCode()) {
                    case "CORE0002":
                        fprintf(STDERR, _("Error : %s\n"), $e->getDcpMessage());
                        self::exceptionHandler($e, false);
                        exit(1);
                        break;

                    case "CORE0003":
                        printf($e->getDcpMessage());
                        exit(0);
                        break;

                    default:
                        fprintf(STDERR, "%s\n", $e->getDcpMessage());
                        self::exceptionHandler($e, false);
                        exit(1);
                }
            } catch (Exception $e) {
                fprintf(STDERR, "%s\n", $e->getDcpMessage());
                self::exceptionHandler($e, false);
                exit(1);
            } /** @noinspection PhpRedundantCatchClauseInspection */
            catch (\Dcp\Exception $e) {
                self::exceptionHandler($e);
            } catch (\Exception $e) {
                self::exceptionHandler($e);
            }
        }
    }

    /**
     * Get all php script included in script directory
     *
     * @return array
     */
    public static function getScripts()
    {
        $apiList = array();
        foreach (new \DirectoryIterator(DEFAULT_PUBDIR . DIRECTORY_SEPARATOR . self::SCRIPTDIR) as $entry) {
            if (preg_match('/^(?<basename>.+)\.php$/', $entry->getFilename(), $m)) {
                $apiList[] = $m['basename'];
            }
        }
        sort($apiList, SORT_STRING | SORT_FLAG_CASE);
        return $apiList;
    }

    public static function initContext()
    {
        $user = new \Anakeen\Core\Account("", \Anakeen\Core\Account::ADMIN_ID);
        $login = self::getArg("login");
        if ($login) {
            if (!$user->setLoginName($login)) {
                throw new \Dcp\Exception(sprintf("Unknow user %s", $login));
            }
        }

        \Anakeen\Core\ContextManager::initContext($user);
    }

    /**
     * Handle exceptions by logging errors or by sending mails
     * depending if the program is used in a CLI or not.
     *
     * @param \Throwable $e
     * @param bool       $callStack If set to false: the error message is minimal.
     *                              Otherwise the error message is the call stack.
     */
    public static function exceptionHandler($e, $callStack = true)
    {
        if ($callStack === true) {
            $errMsg = \Anakeen\Core\LogException::formatErrorLogException($e);
            error_log($errMsg);
        } else {
            $errMsg = $e->getMessage();
        }

        if (!self::isInteractiveCLI()) {
            $expand = array(
                'm' => preg_replace('/^([^\n]*).*/s', '\1', $e->getMessage())
            );
            self::sendEmailError($errMsg, $expand);
        }

        exit(255);
    }


    public static function shutdownHandler()
    {
        global $argv;

        $error = error_get_last();
        if ($error === null) {
            /* No error */
            return;
        }
        /* Process error */
        switch ($error["type"]) {
            case E_ERROR:
                $title = "Runtime Error";
                break;

            case E_CORE_ERROR:
                $title = "Startup Error";
                break;

            case E_PARSE:
                $title = "Parse Error";
                break;

            case E_COMPILE_ERROR:
                $title = "Compile Error";
                break;

            case E_RECOVERABLE_ERROR:
                $title = "Recoverable Error";
                break;

            default:
                return;
        }

        $pid = getmypid();
        $errMsg
            = <<<EOF
$pid> Anakeen $title
EOF;

        if (php_sapi_name() == 'cli' && is_array($argv)) {
            $errMsg .= sprintf("\n%s> Command line arguments: %s", $pid, join(' ', array_map("escapeshellarg", $argv)));
            $errMsg .= sprintf("\n%s> error_log: %s", $pid, ini_get('error_log'));
            $errMsg .= "\n";
        }

        $errMsg
            .= <<<EOF
$pid> Type:    ${error['type']}
$pid> Message: ${error['message']}
$pid> File:    ${error['file']}
$pid> Line:    ${error['line']}
EOF;

        error_log($errMsg);
        if (!self::isInteractiveCLI()) {
            $expand = array(
                'm' => preg_replace('/^([^\n]*).*/s', '\1', $error['message'])
            );
            self::sendEmailError($errMsg, $expand);
        }
    }

    public static function sendEmailError($errMsg, $expand = array())
    {
        $wshError = new \Dcp\WSHMailError($errMsg);
        $wshError->prefix = sprintf('%s %s ', date('c'), php_uname('n'));
        $wshError->addExpand($expand);
        $wshError->autosend();
    }

    public static function isInteractiveCLI()
    {
        if (php_sapi_name() !== 'cli') {
            return false;
        }
        if (function_exists('posix_isatty')) {
            return (posix_isatty(STDIN) || posix_isatty(STDOUT) || posix_isatty(STDERR));
        }
        return true;
    }

    /**
     * return shell commande for ank
     * depending of database (in case of several instances)
     *
     * @param bool   $nice      set to true if want nice mode
     * @param string $userlogin the user login to send command (default is admin)
     * @param bool   $sudo      set to true if want to be send with sudo (need /etc/sudoers correctly configured)
     *
     * @return string the command
     */
    public static function getAnkCmd($nice = false, $userlogin = "admin", $sudo = false)
    {
        $ash = '';
        if ($nice) {
            $ash .= "nice -n +10 ";
        }
        if ($sudo) {
            $ash .= "sudo ";
        }
        $ash .= escapeshellarg(DEFAULT_PUBDIR) . "/" . self::$programName . " ";

        if ($userlogin !== "admin") {
            $u = new \Anakeen\Core\Account("");
            $u->setLoginName($userlogin);
            if (!$u->isAffected()) {
                throw new Exception(sprintf("%s : User \"%s\" not found", self::$programName, $userlogin));
            }
            $ash .= sprintf("--login=\"%s\" ", $u->login);
        }
        return $ash;
    }
}
