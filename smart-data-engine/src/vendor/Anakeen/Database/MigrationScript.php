<?php

namespace Anakeen\Database;

use Anakeen\Core\ContextManager;
use Anakeen\Core\DbManager;
use Anakeen\Core\Internal\SmartElement;

/**
 * Class Execute Migration Xml Scripts
 */
class MigrationScript
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
        return preg_replace_callback(
            "/@([^@]*)@/",
            function ($matches) {
                $p = $matches[1];
                switch ($p) {
                    case "PUBDIR":
                        return getenv("wpub");
                    case "PGSERVICE":
                        return getenv("pgservice_core");
                }
            },
            $content
        );
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
    public static function handleXmlError($errno, $errstr)
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
            "handleXmlError"
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
        if ($this->verbose > 0) {
            printf("Process [%s] \"%s\" \n", $action->getAttribute("id"), $action->getAttribute("label"));
        }
        if ($this->verifyConditions($action)) {
            $this->doProcess($action);
            if (!$this->verifyCheck($action)) {
                throw new \Exception(sprintf("check fail [%s] \"%s\"", $action->getAttribute("id"), $action->getAttribute("label")));
            }
        } else {
            if ($this->verbose > 0) {
                printf("Skip [%s] \"%s\" \n", $action->getAttribute("id"), $action->getAttribute("label"));
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

    protected function verboseCondition(\DOMElement $aTest)
    {
        if ($this->verbose > 1) {
            $content = $aTest->textContent;
            $conditionName = $aTest->parentNode->tagName;
            printf("\tVerify %s/%s \"%s\"", $conditionName, $aTest->nodeName, $aTest->getAttribute("label"));
            if ($content && $this->verbose > 2) {
                printf("\n\t\t[%s]", $content);
            }
        }
    }

    /**
     * @param \DOMElement  $action
     *
     * @param \DOMNodeList $topAssertTag
     *
     * @return bool return true if all conditions are verified
     * @throws \Exception
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
                        $this->verboseCondition($aTest);

                        $this->simpleQuery($sql, $return, true, true);

                        $condition = ($return !== false);
                        if ($this->verbose > 1) {
                            printf(" : %s\n", $condition ? "OK" : "KO");
                        }
                        break;

                    case "sql-assert-empty":
                        $sql = $aTest->textContent;
                        $this->verboseCondition($aTest);
                        $this->simpleQuery($sql, $return, true, true);


                        $condition = ($return === false);
                        if ($this->verbose > 1) {
                            printf(" : %s\n", $condition ? "OK" : "KO");
                        }
                        break;

                    case "sql-assert-false":
                        $sql = $aTest->textContent;
                        $this->verboseCondition($aTest);
                        $this->simpleQuery($sql, $return, true, true);

                        $condition = ($return === 'f');
                        if ($this->verbose > 1) {
                            printf(" : %s\n", $condition ? "OK" : "KO");
                        }
                        break;

                    case "sql-assert-true":
                        $sql = $aTest->textContent;
                        $this->verboseCondition($aTest);
                        $this->simpleQuery($sql, $return, true, true);


                        $condition = ($return === 't');
                        if ($this->verbose > 1) {
                            printf(" : %s\n", $condition ? "OK" : "KO");
                        }

                        break;

                    case "php-assert-false":
                        $this->verboseCondition($aTest);
                        $method = $aTest->getAttribute("callable");
                        $loadContext = ($aTest->getAttribute("load-context") !== "false");
                        $return = $this->callMethod($method, $loadContext);


                        $condition = ($return === false);
                        if ($this->verbose > 1) {
                            printf(" : %s\n", $condition ? "OK" : "KO");
                        }
                        break;

                    case "php-assert-true":
                        $this->verboseCondition($aTest);
                        $method = $aTest->getAttribute("callable");
                        $loadContext = ($aTest->getAttribute("load-context") !== "false");
                        $return = $this->callMethod($method, $loadContext);


                        $condition = ($return === true);
                        if ($this->verbose > 1) {
                            printf(" : %s\n", $condition ? "OK" : "KO");
                        }
                        break;

                    case "php-assert-code-return-false":
                        $this->verboseCondition($aTest);
                        $code = $aTest->textContent;
                        $loadContext = ($aTest->getAttribute("load-context") !== "false");
                        $return = $this->callPhpCode($code, $loadContext);

                        $condition = ($return === false);
                        if ($this->verbose > 1) {
                            printf(" : %s\n", $condition ? "OK" : "KO");
                        }
                        break;

                    case "php-assert-code-return-true":
                        $this->verboseCondition($aTest);
                        $code = $aTest->textContent;
                        $loadContext = ($aTest->getAttribute("load-context") !== "false");
                        $return = $this->callPhpCode($code, $loadContext);

                        $condition = ($return === true);
                        if ($this->verbose > 1) {
                            printf(" : %s\n", $condition ? "OK" : "KO");
                        }
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
            throw new \Anakeen\Exception(sprintf("Error when call \"%s\"", $method));
        }
        if ($this->verbose > 1) {
            print " : OK\n";
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
        try {
            $return = eval($code);
            if ($this->verbose > 1) {
                print " : OK\n";
            }
        } catch (\Exception $e) {
            if ($this->verbose > 1) {
                if ($this->verbose > 2) {
                    throw $e;
                } else {
                    printf(" : KO : %s\n", $e->getMessage());
                    throw new \Anakeen\Exception("Fail eval code");
                }
            }
        }
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
                if ($this->verbose > 1) {
                    printf("\tProcess %s \"%s\"", $aProc->nodeName, $aProc->getAttribute("label"));
                }
                try {
                    switch ($aProc->nodeName) {
                        case "sql-query":
                            $sql = $aProc->textContent;
                            $sqlFile = $aProc->getAttribute("file");
                            if ($sqlFile) {
                                $sql = file_get_contents(sprintf("%s/%s", getenv("wpub"), $sqlFile)) . $sql;
                            }
                            $this->simpleQuery($sql, $return, true, true);


                            break;


                        case "bash-code":
                            $bash = $aProc->textContent;
                            $this->shellExec($bash);

                            break;

                        case "php":
                            $method = $aProc->getAttribute("callable");
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
     * Execute Shell command
     *
     * @param string $cmd
     *
     * @return bool
     * @throws \Exception
     */
    protected function shellExec($cmd)
    {
        if ($this->verbose > 2) {
            printf("\n\t\tExecute %s\n", $cmd);
        }
        exec($cmd, $output, $return);

        if ($this->verbose > 2) {
            printf("\t\t Output %s\n\t\t", print_r($output, true));
        }
        if ($this->verbose > 1) {
            printf(" : %s\n", ($return === 0) ? "OK" : $return);
        }
        if ($return !== 0) {
            throw new \Exception(sprintf("Shell Error [%s] : %s", $cmd, print_r($output, true)));
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
     * @return void
     * @throws \Exception
     */
    protected function simpleQuery($query, &$result = array(), $singlecolumn = false, $singleresult = false)
    {
        DbManager::query($query, $result, $singlecolumn, $singleresult);
    }
}
