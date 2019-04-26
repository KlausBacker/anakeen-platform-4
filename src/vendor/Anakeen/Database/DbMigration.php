<?php

namespace Anakeen\Database;

use Anakeen\Core\ContextManager;
use Anakeen\Core\DbManager;
use Anakeen\Core\Internal\SmartElement;

/**
 * Class Execute Migration Xml Scripts
 */
class DbMigration
{
    protected $verbose = 1;
    protected $xmlFile = '';
    protected $dryRunActivated = false;
    /**
     * @var \DOMDocument
     */
    protected $dom = null;

    /**
     * @param string $file XML input file
     */
    public function __construct($file)
    {
        if (!file_exists($file)) {
            throw new \Exception(sprintf("Migration file %s not found", $file));
        }
        $this->xmlFile = $file;
    }

    /**
     * To display messages
     *
     * @param int $level 0,1 or 2
     */
    public function verbose($level)
    {
        $this->verbose = (int)$level;
    }

    /**
     * database queries are not commited
     */
    public function dryRun()
    {
        $this->dryRunActivated = true;
    }

    /**
     * Parse and execute the xmlinput file
     *
     * @throws \Exception
     */
    public function execute()
    {
        $this->dom = new \DOMDocument();
        $this->xmlLoader($this->parseFile($this->xmlFile));

        if ($this->verbose > 0 && $this->dryRunActivated) {
            printf("DryRun activated\n");
        }
        $this->simpleQuery("begin");
        if ($this->dom === false) {
            throw new \Exception(sprintf("Migration file %s corrupted", $this->xmlFile));
        }

        $actions = $this->dom->getElementsByTagName("action");

        foreach ($actions as $aAction) {
            $this->analyzeAction($aAction);
        }
        if ($this->dryRunActivated) {
            $this->simpleQuery("rollback");
        } else {
            $this->simpleQuery("commit");
        }
    }

    protected function parseFile($xmlFile)
    {
        $content = file_get_contents($xmlFile);
        return preg_replace_callback("/@([^@]*)@/", function ($matches) {
            $p = $matches[1];
            switch ($p) {
                case "PUBDIR":
                    return getenv("wpub");
                case "PGSERVICE":
                    return getenv("pgservice_core");
                default:
                    /** @noinspection PhpUndefinedFunctionInspection */
                    $wv = \wiff_getParamValue($p);
                    return $wv;
            }
        }
            , $content);
    }

    /**
     * use to trap XML parsing error : raise exception
     *
     * @param int    $errno  error number
     * @param string $errstr error message
     *
     * @return bool
     * @throws \DOMException
     */
    public static function HandleXmlError($errno, $errstr)
    {
        if ($errno == E_WARNING && (substr_count($errstr, "DOMDocument::load") > 0)) {
            throw new \DOMException($errstr);
        } else {
            return false;
        }
    }

    /**
     * to get xml warning as Exception
     *
     * @param string $strXml
     *
     * @return \DOMDocument
     */
    protected function xmlLoader($strXml)
    {
        set_error_handler(array(
            __CLASS__,
            "HandleXmlError"
        ));
        $this->dom->loadXml($strXml);
        restore_error_handler();
        return $this->dom;
    }

    /**
     * Process an "action" tag
     *
     * @param \DOMElement $action
     *
     * @throws \Exception
     */
    protected function analyzeAction(\DOMElement $action)
    {
        if ($this->verifyConditions($action)) {
            if ($this->verbose > 0) {
                printf("Process [%s] \"%s\" \n", $action->getAttribute("id"), $action->getAttribute("title"));
            }
            $this->doProcess($action);
            if (!$this->verifyCheck($action)) {
                throw new \Exception(sprintf("check fail [%s] \"%s\"", $action->getAttribute("id"), $action->getAttribute("title")));
            }
        } else {
            if ($this->verbose > 0) {
                printf("Skip [%s] \"%s\" \n", $action->getAttribute("id"), $action->getAttribute("title"));
            }
        }
    }

    /**
     * @param \DOMElement $action
     *
     * @return bool return true if all confition are verified
     * @throws \Exception
     *
     */
    protected function verifyConditions(\DOMElement $action)
    {
        $conditionTag = $action->getElementsByTagName("condition");
        return $this->verifyAssertion($action, $conditionTag);
    }

    /**
     * @param \DOMElement $action
     *
     * @return bool return true if all conditions are verified
     * @throws \Exception
     *
     */
    protected function verifyCheck(\DOMElement $action)
    {
        $conditionTag = $action->getElementsByTagName("check");

        return $this->verifyAssertion($action, $conditionTag);
    }

    /**
     * @param \DOMElement $action
     *
     * @return bool return true if all conditions are verified
     * @throws \Exception
     *
     */
    protected function verifyAssertion(\DOMElement $action, \DOMNodeList $topAssertTag)
    {
        $condition = false;
        /**
         * @var \DOMElement $aCondition
         */
        foreach ($topAssertTag as $aCondition) {
            $ol = $aCondition->getAttribute("ol");
            if (!$ol) {
                $ol = "and";
            }
            if ($ol != 'or' && $ol != 'and') {
                throw new \Exception(sprintf("incorrect \"ol\" attribute \"%s\"\n%s", $ol, $action->ownerDocument->saveXML($aCondition)));
            }
            $tests = $aCondition->childNodes;
            foreach ($tests as $aTest) {
                if ($aTest->nodeType !== XML_ELEMENT_NODE) {
                    continue;
                }
                /**
                 * @var \DOMElement $aTest
                 */
                switch ($aTest->nodeName) {
                    case "sql-assert-not-empty":
                        $sql = $aTest->textContent;
                        $this->simpleQuery($sql, $return, true, true);
                        if ($this->verbose > 1) {
                            printf("\tVerify %s/%s [%s] : %s\n", $aCondition->nodeName, $aTest->nodeName, $sql, $return);
                        }
                        $condition = ($return !== false);
                        break;

                    case "sql-assert-empty":
                        $sql = $aTest->textContent;
                        $this->simpleQuery($sql, $return, true, true);
                        if ($this->verbose > 1) {
                            printf("\tVerify %s/%s [%s] : %s\n", $aCondition->nodeName, $aTest->nodeName, $sql, $return);
                        }

                        $condition = ($return === false);
                        break;

                    case "sql-assert-false":
                        $sql = $aTest->textContent;
                        $this->simpleQuery($sql, $return, true, true);
                        if ($this->verbose > 1) {
                            printf("\tVerify %s/%s [%s] : %s\n", $aCondition->nodeName, $aTest->nodeName, $sql, $return);
                        }

                        $condition = ($return === 'f');
                        break;

                    case "sql-assert-true":
                        $sql = $aTest->textContent;
                        $this->simpleQuery($sql, $return, true, true);
                        if ($this->verbose > 1) {
                            printf("\tVerify %s/%s [%s] : %s\n", $aCondition->nodeName, $aTest->nodeName, $sql, $return);
                        }

                        $condition = ($return === 't');

                        break;

                    case "php-assert-false":
                        $method = $aTest->getAttribute("method");
                        $loadContext = ($aTest->getAttribute("load-context") !== "false");
                        $return = $this->callMethod($method, $loadContext);
                        if ($this->verbose > 1) {
                            printf("\tVerify %s/%s [%s] : %s\n", $aCondition->nodeName, $aTest->nodeName, $method, $return);
                        }

                        $condition = ($return === false);
                        break;

                    case "php-assert-true":
                        $method = $aTest->getAttribute("method");
                        $loadContext = ($aTest->getAttribute("load-context") !== "false");
                        $return = $this->callMethod($method, $loadContext);
                        if ($this->verbose > 1) {
                            printf("\tVerify %s/%s [%s] : %s\n", $aCondition->nodeName, $aTest->nodeName, $method, $return);
                        }

                        $condition = ($return === true);
                        break;

                    case "php-assert-code-return-false":
                        $code = $aTest->textContent;
                        $loadContext = ($aTest->getAttribute("load-context") !== "false");
                        $return = $this->callPhpCode($code, $loadContext);
                        if ($this->verbose > 1) {
                            printf("\tVerify %s/%s [%s] : %s\n", $aCondition->nodeName, $aTest->nodeName, $code, $return);
                        }

                        $condition = ($return === false);
                        break;

                    case "php-assert-code-return-true":
                        $code = $aTest->textContent;
                        $loadContext = ($aTest->getAttribute("load-context") !== "false");
                        $return = $this->callPhpCode($code, $loadContext);
                        if ($this->verbose > 1) {
                            printf("\tVerify %s/%s [%s] : %s\n", $aCondition->nodeName, $aTest->nodeName, $code, $return);
                        }

                        $condition = ($return === true);
                        break;

                    default:
                        throw new \Exception(sprintf("Unknow condition type \"%s\"\n%s", $aTest->nodeName, $action->ownerDocument->saveXML($aCondition)));
                }
                if ($condition === true and $ol === "or") {
                    return true;
                } elseif ($condition === false and $ol === "and") {
                    return false;
                }
            }
        }

        return $condition;
    }

    /**
     * Initialize Dynacase context
     */
    protected function initContext()
    {
        static $init = false;

        if ($init === false) {
            $user = new \Anakeen\Core\Account("", \Anakeen\Core\Account::ADMIN_ID);
            ContextManager::initContext($user);
            $init = true;
        }
    }

    /**
     * Call static Php method
     *
     * @param string $method
     * @param bool   $initContext
     *
     * @return string
     * @throws \Exception
     */
    protected function callMethod($method, $initContext = true)
    {
        if ($initContext) {
            $this->initContext();
        }
        $doc = new SmartElement();
        if (!SmartElement::seemsMethod($method)) {
            throw new \Anakeen\Exception(sprintf("Incorrect method syntax \"%s\"", $method));
        }
        $return = $doc->applyMethod($method, $def = "", $index = -1, $bargs = array(), $mapArgs = array(), $err);
        if ($err) {
            throw new \Anakeen\Exception(sprintf("Unknow method type \"%s\"", $method));
        }
        return $return;
    }

    /**
     * Eval PHP code
     *
     * @param string $code
     * @param bool   $initContext
     *
     * @return mixed
     */
    protected function callPhpCode($code, $initContext = true)
    {
        if ($initContext) {
            $this->initContext();
        }
        $return = eval($code);
        return $return;
    }

    /**
     * Execute a "process" tag
     *
     * @param \DOMElement $action
     *
     * @return bool return true if all confition are verified
     * @throws \Exception
     *
     */
    protected function doProcess(\DOMElement $action)
    {
        $processTag = $action->getElementsByTagName("process");
        /**
         * @var \DOMElement $aProcess
         */
        foreach ($processTag as $aProcess) {
            $tests = $aProcess->childNodes;
            foreach ($tests as $aProc) {
                if ($aProc->nodeType !== XML_ELEMENT_NODE) {
                    continue;
                }
                /**
                 * @var \DOMElement $aProc
                 */
                $stopOnError = ($aProc->getAttribute("stop-on-error") !== "false");
                try {

                    switch ($aProc->nodeName) {
                        case "sql-query":
                            $sql = $aProc->textContent;
                            $sqlFile = $aProc->getAttribute("file");
                            if ($sqlFile) {
                                $sql = file_get_contents(sprintf("%s/%s", getenv("wpub"), $sqlFile)) . $sql;
                            }
                            $this->simpleQuery($sql, $return, true, true);
                            if ($this->verbose > 1) {
                                if ($sqlFile) {
                                    $sqlCmd = "\\i " . $sqlFile . "\n" . $aProc->textContent;
                                } else {
                                    $sqlCmd = $aProc->textContent;
                                }
                                printf("Process %s [%s] : %s\n", $aProc->nodeName, $sqlCmd, $return);
                            }

                            break;

                        case "wsh":
                            $api = $aProc->getAttribute("api");
                            $app = $aProc->getAttribute("api");
                            if (!$api && !$app) {
                                throw new \Exception(sprintf("No api or app set in wsh \"%s\"\n%s", $aProc->nodeName, $action->ownerDocument->saveXML($aProcess)));
                            }
                            $args = array();
                            foreach ($aProc->attributes as $attr) {
                                $args[$attr->nodeName] = $attr->nodeValue;
                            }
                            $this->wshApi($args);

                            break;

                        case "bash":
                            $bash = $aProc->textContent;
                            $this->bashExec($bash);

                            break;

                        case "php":
                            $method = $aProc->getAttribute("method");
                            $loadContext = ($aProc->getAttribute("load-context") !== "false");

                            $this->callMethod($method, $loadContext);

                            break;

                        case "php-code":
                            $code = $aProc->textContent;
                            $loadContext = ($aProc->getAttribute("load-context") !== "false");

                            $this->callPhpCode($code, $loadContext);

                            break;

                        default:
                            throw new \Exception(sprintf("Unknow process type \"%s\"\n%s", $aProc->nodeName, $action->ownerDocument->saveXML($aProcess)));
                    }
                } catch (Exception $e) {
                    if ($stopOnError) {
                        throw $e; // resend else continue

                    } else {

                        if ($this->verbose > 0) {
                            printf("Error process  : %s\n", $e->getMessage());
                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * Execute bash process
     *
     * @param string $cmd
     *
     * @return bool
     * @throws \Exception
     */
    protected function bashExec($cmd)
    {
        if ($this->verbose > 1) {
            printf("\tExecute %s\n", $cmd);
        }
        exec($cmd, $output, $return);

        if ($this->verbose > 2) {
            printf("\t Output %s\n", print_r($output, true));
        }
        if ($this->verbose > 1) {
            printf("\t Return status %s\n", ($return === 0) ? "OK" : $return);
        }
        if ($return !== 0) {
            throw new \Exception(sprintf("Bash Error [%s] : %s", $cmd, print_r($output, true)));
        }
        return ($return === 0);
    }

    /**
     * Execute wsh process
     *
     * @param array $args indexed parameters for wsh
     *
     * @return bool
     * @throws \Exception
     */
    protected function wshApi(array $args)
    {

        $cmd = sprintf("%s/wsh.php", getenv("wpub"));
        foreach ($args as $k => $v) {
            $cmd .= sprintf(" --%s=%s", $k, escapeshellarg($v));
        }
        if ($this->verbose > 1) {
            printf("\tExecute %s\n", $cmd);
        }
        exec($cmd, $output, $return);

        if ($this->verbose > 2) {
            printf("\t Output %s\n", print_r($output, true));
        }
        if ($this->verbose > 1) {
            printf("\t Return status %s\n", ($return === 0) ? "OK" : $return);
        }
        if ($return !== 0) {
            throw new \Exception(sprintf("Wsh Error [%s] : %s", $cmd, print_r($output, true)));
        }
        return ($return === 0);
    }

    /**
     * Send sql query to database
     *
     * @param string $query
     * @param array  $result
     * @param bool   $singlecolumn
     * @param bool   $singleresult
     *
     * @return string
     * @throws \Exception
     */
    protected function simpleQuery($query, &$result = array(), $singlecolumn = false, $singleresult = false)
    {
        DbManager::query($query, $result, $singlecolumn, $singleresult);
        return;
        static $dbid = null;

        if ($dbid === null) {
            $dbaccess = getenv("pgservice_core");
            if (!$dbaccess) {
                throw new \Exception(sprintf("Cannot access \"pgservice_core\" en variable"));
            }
            $dbid = pg_connect(sprintf('service=%s', $dbaccess));
            if (!$dbid) {
                throw new \Exception(sprintf("Cannot connect to database  \"%s\"", $dbaccess));
            }
        }

        $err = '';

        $result = array();
        $r = pg_query($dbid, $query);
        if ($r) {
            if (pg_numrows($r) > 0) {
                if ($singlecolumn) {
                    $result = pg_fetch_all_columns($r, 0);
                } else {
                    $result = pg_fetch_all($r);
                }
                if ($singleresult) {
                    $result = $result[0];
                }
            } else {
                if ($singleresult && $singlecolumn) {
                    $result = false;
                }
            }
        } else {
            $err = sprintf("%s [%s]", pg_last_error($dbid), $query);
        }

        if ($err) {

            throw new \Exception($err);
        }

        return $err;
    }
}
