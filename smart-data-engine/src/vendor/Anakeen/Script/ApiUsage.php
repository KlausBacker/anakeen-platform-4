<?php

namespace Anakeen\Script;

use Anakeen\Core\ContextManager;

/**
 * Verify arguments for wsh programs
 *
 * @class ApiUsage
 * @brief Verify arguments for wsh programs
 * @code
 * $usage = new \Anakeen\Script\ApiUsage();
 * $usage->setDescriptionText("Refresh documents ");
 * $usage->addRequiredParameter("famid", "the family filter");
 * $usage->addOptionalParameter("revision", "use all revision - default is no", array(
 * "yes",
 * "no"
 * ));
 * $usage->addOptionalParameter("save", "use modify default is light", array(
 * "complete",
 * "light",
 * "none"
 * ));
 * $usage->verify();
 * @endcode
 */
class ApiUsage
{
    const THROW_EXITHELP = 1988;
    const GET_USAGE = null;
    /**
     * usage text
     *
     * @var string
     */
    private $text = '';
    /**
     * optionals arguments
     *
     * @var array
     */
    private $optArgs = array();
    /**
     * empty arguments
     *
     * @var array
     */
    private $emptyArgs = array();
    /**
     * needed arguments
     *
     * @var array
     */
    private $needArgs = array();
    /**
     * hidden arguments
     *
     * @var array
     */
    private $hiddenArgs = array();

    /**
     * strict mode
     *
     * @var boolean
     */
    protected $strict = true;
    /**
     * force throw exception when verify fail instead of exitError
     *
     * @var boolean
     */
    protected $useException = false;

    /**
     * init action
     */
    public function __construct()
    {
        $this->addHiddenParameter("script", "api file to use");
        $this->addOptionalParameter('login', "user login name to execute function", null, "admin");
        $this->addEmptyParameter('help', "Show usage");
    }

    /**
     * Restriction callback to verify a scalar value
     *
     * @param string                   $argVal   argument value
     * @param string                   $argName  argument name
     * @param \Anakeen\Script\ApiUsage $apiUsage current apiUsage object
     *
     * @return string
     */
    public static function isScalar($argVal, $argName, $apiUsage)
    {
        $err = "";
        if (!is_scalar($argVal)) {
            if (is_array($argVal)) {
                if (!isset($_FILES[$argName])) {
                    $err = sprintf("Argument doesn't support multiple value (values got are [%s])", implode(",", $argVal));
                }
            } else {
                $err = sprintf("Value type %s isn't authorized for argument, must be a scalar", gettype($argVal));
            }
        }
        return $err;
    }

    /**
     * Restriction callback to verify an array value
     *
     * @param string                   $argVal   argument value
     * @param string                   $argName  argument name
     * @param \Anakeen\Script\ApiUsage $apiUsage current apiUsage object
     *
     * @return string
     */
    public static function isArray($argVal, $argName, $apiUsage)
    {
        $err = "";
        if (!is_array($argVal)) {
            $err = sprintf("Value type %s isn't authorized for argument, must be an array", gettype($argVal));
        }
        return $err;
    }



    /**
     * add textual definition of program
     *
     * @api add textual definition of program
     *
     * @param string $text usage text
     *
     * @return void
     */
    public function setDefinitionText($text)
    {
        $this->text = $text;
    }



    /**
     * add hidden argument (private arg not see them in usage)
     *
     * @api add an empty parameter
     *
     * @param string $argName       argument name
     * @param string $argDefinition argument définition
     *
     * @return string argument value
     */
    public function addHiddenParameter($argName, $argDefinition)
    {
        $this->hiddenArgs[] = array(
            "name" => $argName,
            "def" => $argDefinition
        );
        return $this->getArgumentValue($argName);
    }



    /**
     * add needed argument
     *
     * @api add needed argument
     *
     * @param string         $argName       argument name
     * @param string         $argDefinition argument définition
     * @param array|callable $restriction   optional enumeration for argument
     *
     * @return string argument value
     */
    public function addRequiredParameter($argName, $argDefinition, $restriction = null)
    {
        $this->needArgs[] = array(
            "name" => $argName,
            "def" => $argDefinition,
            "default" => null,
            "restriction" => $restriction
        );
        return $this->getArgumentValue($argName);
    }


    /**
     * add optional argument
     *
     * @api add optional argument
     *
     * @param string         $argName       argument name
     * @param string         $argDefinition argument definition
     * @param array|callable $restriction   optional enumeration for argument
     * @param string         $default       default value if no value set
     *
     * @return string argument value
     */
    public function addOptionalParameter($argName, $argDefinition, $restriction = null, $default = null)
    {
        $this->optArgs[] = array(
            "name" => $argName,
            "def" => $argDefinition,
            "default" => $default,
            "restriction" => $restriction
        );
        return $this->getArgumentValue($argName, $default);
    }



    /**
     * add empty argument (argument with boolean value)
     *
     * @api add empty argument (argument with boolean value)
     *
     * @param string $argName       argument name
     * @param string $argDefinition argument definition
     *
     * @return bool true is option is set
     */
    public function addEmptyParameter($argName, $argDefinition = "")
    {
        $this->emptyArgs[] = array(
            "name" => $argName,
            "def" => $argDefinition,
            "default" => null,
            "restriction" => null
        );
        return $this->getArgumentValue($argName, false);
    }

    /**
     * Return value of argument key
     *
     * @param string $key          the identifier
     * @param string $defaultValue value to return if value is empty
     *
     * @return mixed|string
     */
    protected function getArgumentValue($key, $defaultValue = '')
    {
        $val = ShellManager::getArg($key);
        if ($val === null) {
            return $defaultValue;
        }
        return $val;
    }

    /**
     * get usage for a specific argument
     *
     * @param array $args  argument
     * @param bool  $empty flag to see if argument array as values or not
     *
     * @return string
     */
    private function getArgumentText(array $args, $empty = false)
    {
        $usage = '';
        foreach ($args as $arg) {
            $res = '';

            if ($this->isCallable($arg["restriction"])) {
                $res = call_user_func($arg["restriction"], \Anakeen\Script\ApiUsage::GET_USAGE, $arg["name"], $this);
            } elseif (!empty($arg["restriction"]) && is_array($arg["restriction"])) {
                $res = ' [' . implode('|', $arg["restriction"]) . ']';
            }
            $default = "";
            if ($arg["default"] !== null) {
                $default = sprintf(", default is '%s'", print_r($arg["default"], true));
            }
            $string = "\t--" . $arg["name"] . ($empty ? " (%s) " : "=<%s>");

            $usage .= sprintf("$string%s%s\n", $arg["def"], $res, $default);
        }
        return $usage;
    }

    protected function isCallable($f)
    {
        if (empty($f)) {
            return false;
        }
        if (!is_callable($f, true)) {
            return false;
        }
        if (is_object($f) && ($f instanceof \Closure)) {
            return true;
        }
        if (is_array($f) && (is_scalar($f[0]))) {
            return false;
        }
        if (is_callable($f, false)) { // many many time to search
            return true;
        }

        return false;
    }

    /**
     * return usage text for the action
     *
     * @return string
     */
    public function getUsage()
    {
        $usage = $this->text;
        $usage .= "\nUsage:\n";
        $usage .= $this->getArgumentText($this->needArgs);
        $usage .= "   Options:\n";
        $usage .= $this->getArgumentText($this->optArgs);
        $usage .= $this->getArgumentText($this->emptyArgs, true);
        return $usage;
    }

    /**
     * exit when error
     *
     * @param string $error message error
     *
     * @throws UsageException
     * @return void
     */
    public function exitError($error = '')
    {
        if ($error != '') {
            $error .= "\n";
        }
        $usage = $this->getUsage();

        if (!$this->useException) {
            if (!empty($_SERVER['HTTP_HOST'])) {
                $usage = str_replace('--', '&', $usage);
                $error .= '<pre>' . htmlspecialchars($usage) . '</pre>';
            } else {
                $error .= $usage;
            }
            if ($this->getArgumentValue("help") == true) {
                throw new UsageException("CORE0003", $error, $usage);
            }
            if (!empty($_SERVER['HTTP_HOST'])) {
                ContextManager::exitError($error);
            } else {
                throw new UsageException("CORE0002", $error, $usage);
            }
        } else {
            // no usage when use exception mode
            throw new UsageException("CORE0002", $error, $usage);
        }
    }

    /**
     * list hidden keys
     *
     * @return array
     */
    protected function getHiddenKeys()
    {
        $keys = array();
        foreach ($this->hiddenArgs as $v) {
            $keys[] = $v["name"];
        }
        return $keys;
    }



    /**
     * set strict mode
     *
     * @api   set strict mode
     *
     * @param boolean $strict strict mode
     *
     * @brief if false additionnal arguments are ignored, default is true
     *
     * @return void
     */
    public function setStrictMode($strict = true)
    {
        $this->strict = $strict;
    }

    /**
     * verify if wsh program argument are valids. If not wsh exit
     *
     * @api Verify if wsh's program arguments are valid
     *
     * @param bool $useException if true throw ApiUsageException when verify is not successful
     *
     * @return void
     */
    public function verify($useException = false)
    {
        $this->useException = $useException;
        if ($this->getArgumentValue("help") == true) {
            $this->exitError();
        }
        foreach ($this->needArgs as $arg) {
            $value = $this->getArgumentValue($arg["name"]);
            if ($value === '' || is_bool($value)) {
                $error = sprintf("argument '%s' expected\n", $arg["name"]);

                $this->exitError($error);
            }
        }
        $allArgs = array_merge($this->needArgs, $this->optArgs, $this->emptyArgs);
        $argsKey = $this->getHiddenKeys();

        foreach ($allArgs as $arg) {
            $value = $this->getArgumentValue($arg["name"], null);
            if ($value !== null) {
                if ($this->isCallable($arg["restriction"])) {
                    $error = call_user_func($arg["restriction"], $value, $arg["name"], $this);
                } else {
                    $error = \Anakeen\Script\ApiUsage::isScalar($value, $arg["name"], $this);
                }
                if ($error) {
                    $this->exitError(sprintf("Error checking argument \"%s\" : %s", $arg["name"], $error));
                }

                if (is_array($arg["restriction"]) && !empty($arg["restriction"]) && !$this->isCallable($arg["restriction"])) {
                    $error = $this->matchValues($value, $arg["restriction"]);
                    if ($error) {
                        $this->exitError(sprintf("Error for argument '%s' : %s", $arg["name"], $error));
                    }
                }
            }
            $argsKey[] = $arg["name"];
        }
        if ($this->strict) {
            $scriptArgs = ShellManager::getArgs();
            foreach ($scriptArgs as $k => $v) {
                if (!in_array($k, $argsKey)) {
                    $error = sprintf("argument '%s' is not defined\n", $k);

                    $this->exitError($error);
                }
            }
        }
    }

    /**
     * @param $value
     * @param $restrictions
     *
     * @return string
     */
    public static function matchValues($value, $restrictions)
    {
        $error = "";
        $values = (!is_array($value)) ? array(
            $value
        ) : $value;
        foreach ($values as $aValue) {
            if (!in_array($aValue, $restrictions)) {
                $error = sprintf("argument must be one of these values : %s\n", implode(", ", $restrictions));
            }
        }
        return $error;
    }
}
