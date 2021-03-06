<?php

namespace Anakeen\Core\SmartStructure\Callables;

class ParseFamilyFunction
{
    public $functionName = '';
    public $appName = '';
    public $funcCall = '';
    public $inputString = '';
    public $outputString = '';
    /**
     * @var InputArgument[]
     */
    public $inputs = array();
    public $outputs = array();
    protected $error = '';
    protected $firstParenthesis;
    protected $lastParenthesis;
    protected $lastSemiColumn;

    public function getError()
    {
        return $this->error;
    }

    protected function setError($error)
    {
        return $this->error = $error;
    }

    protected function initParse($funcCall)
    {
        $this->funcCall = $funcCall;
        $this->firstParenthesis = strpos($funcCall, '(');
        $this->lastParenthesis = strrpos($funcCall, ')');
        $this->lastSemiColumn = strrpos($funcCall, ':');
        $this->inputs = array();
        $this->outputs = array();
        $this->outputString = '';
        $this->inputString = '';
    }

    protected function checkParenthesis()
    {
        if (($this->firstParenthesis === false) || ($this->lastParenthesis === false) || ($this->firstParenthesis >= $this->lastParenthesis)) {
            $this->setError(\ErrorCode::getError('ATTR1201', $this->funcCall));
            return false;
        }

        if ($this->lastSemiColumn > $this->lastParenthesis) {
            $spaceUntil = $this->lastSemiColumn;
        } else {
            $spaceUntil = strlen($this->funcCall);
        }

        for ($i = $this->lastParenthesis + 1; $i < $spaceUntil; $i++) {
            $c = $this->funcCall[$i];
            if ($c != ' ') {
                $this->setError(\ErrorCode::getError('ATTR1201', $this->funcCall));
                return false;
            }
        }

        return true;
    }

    /**
     * @static
     *
     * @param      $methCall
     * @param bool $noOut
     *
     * @return parseFamilyFunction
     */
    public function parse($methCall, $noOut = false)
    {
        $this->initParse($methCall);
        $funcName = trim(substr($methCall, 0, $this->firstParenthesis));
        if (strpos($funcName, ':')) {
            list($appName, $funcName) = explode(':', $funcName, 2);
        } else {
            $appName = '';
        }

        if ($this->checkParenthesis()) {
            if ((!$noOut) && ($this->lastSemiColumn < $this->lastParenthesis)) {
                $this->setError(\ErrorCode::getError('ATTR1206', $methCall));
            } else {
                if (!$this->isPHPName($funcName)) {
                    $this->setError(\ErrorCode::getError('ATTR1202', $funcName));
                } elseif (!preg_match('/^[a-z0-9_]*$/i', $appName)) {
                    $this->setError(\ErrorCode::getError('ATTR1202', $funcName));
                } else {
                    $this->functionName = $funcName;
                    $this->appName = $appName;
                    $inputString = substr($methCall, $this->firstParenthesis + 1, ($this->lastParenthesis - $this->firstParenthesis - 1));
                    $this->inputString = $inputString;

                    $this->parseArguments();
                    $this->parseOutput();
                }
            }
        }

        return $this;
    }

    protected function parseArguments()
    {
        for ($i = 0; $i < strlen($this->inputString); $i++) {
            $c = $this->inputString[$i];

            if ($c === '"') {
                $this->parseDoubleQuote($i);
            } elseif ($c === "'") {
                $this->parseSimpleQuote($i);
            } elseif ($c === ',') {
            } elseif ($c === ' ') {
                // skip
            } else {
                $this->parseArgument($i);
            }
        }
    }

    protected function parseOutput()
    {
        if ($this->lastSemiColumn > $this->lastParenthesis) {
            $this->outputString = trim(substr($this->funcCall, $this->lastSemiColumn + 1));
        }
        if ($this->outputString) {
            $outputs = explode(',', $this->outputString);

            foreach ($outputs as $output) {
                $output = trim($output);
                if (preg_match("/(.*){(.*)}/", $output, $reg)) {
                    $this->outputs[$reg[2]] = $reg[1];
                } else {
                    $this->outputs[count($this->outputs)] = $output;
                }
                if (!$this->isAlphaNumOutAttribute($output)) {
                    $this->setError(\ErrorCode::getError('ATTR1207', $this->funcCall));
                }
            }
        }
    }

    protected function isAlphaNum($s)
    {
        return preg_match('/^[a-z_][a-z0-9_]*$/i', $s);
    }

    protected function isAlphaNumOutAttribute($s)
    {
        return preg_match('/^[a-z_?][a-z0-9_\[\]]*$/i', $s);
    }

    protected function isPHPName($s)
    {
        return preg_match('/^[a-z_][a-z0-9_]*$/i', $s);
    }

    protected function isPHPClassName($s)
    {
        return preg_match('/^([a-z_][a-z0-9_]*\\\\)*[a-z_][a-z0-9_]*$/i', $s);
    }

    private function gotoNextArgument(&$index)
    {
        for ($i = $index; $i < strlen($this->inputString); $i++) {
            $c = $this->inputString[$i];

            if ($c == ',') {
                break;
            } elseif ($c == " ") {
                //skip
            } else {
                $this->setError($this->setError(\ErrorCode::getError('ATTR1204', strlen($this->functionName) + 1 + $i, $this->funcCall)));
            }
        }
        $index = $i;
    }

    /**
     * analyze single misc argument
     *
     * @param int $index index to start analysis string
     *
     * @return void
     */
    protected function parseArgument(&$index)
    {
        $arg = '';
        for ($i = $index; $i < strlen($this->inputString); $i++) {
            $c = $this->inputString[$i];

            if ($c == ',') {
                break;
            } else {
                $arg .= $c;
            }
        }
        $index = $i;
        $arg = trim($arg);

        $key = null;
        $type = "any";

        if (preg_match('/^(.*)::([a-z]+)$/i', $arg, $reg)) {
            $arg = $reg[1];
            $type = $reg[2];
        }

        if (preg_match('/^{([a-z_][a-z0-9_]+)}(.*)$/i', $arg, $reg)) {
            $key = $reg[1];
            $arg = $reg[2];
        }

        if ($type === "any" && !preg_match('/^[a-z_][a-z0-9_]*$/i', $arg)) {
            $arg = trim($arg, '"');
            $type = "string";
        }

        if ($key === null) {
            $this->inputs[] = new InputArgument($arg, $type);
        } else {
            $this->inputs[$key] = new InputArgument($arg, $type);
        }
    }

    /**
     * analyze single double quoted text argument
     *
     * @param int $index index to start analysis string
     *
     * @return void
     */
    protected function parseDoubleQuote(&$index)
    {
        $arg = '';
        $doubleQuoteDetected = false;
        $c = $this->inputString[$index];
        if ($c != '"') {
            $this->setError($this->setError(\ErrorCode::getError('ATTR1204', strlen($this->functionName) + 1 + $index, $this->funcCall)));
        }
        for ($i = $index + 1; $i < strlen($this->inputString); $i++) {
            $cp = $c;
            $c = $this->inputString[$i];

            if ($c == '"') {
                if ($cp == '\\') {
                    $arg = substr($arg, 0, -1);
                    $arg .= $c;
                } else {
                    $doubleQuoteDetected = true;
                    break;
                }
            } else {
                $arg .= $c;
            }
        }
        $index = $i;

        if (!$doubleQuoteDetected) {
            $this->setError($this->setError(\ErrorCode::getError('ATTR1204', strlen($this->functionName) + 1 + $index, $this->funcCall)));
        } else {
            $index++;
            $this->gotoNextArgument($index);
        }
        $key = null;
        if (preg_match('/^{([a-z_][a-z0-9_]+)}(.*)$/i', $arg, $reg)) {
            $key = $reg[1];
            $arg = $reg[2];
        }
        if ($key === null) {
            $this->inputs[] = new InputArgument($arg, "string");
        } else {
            $this->inputs[$key] = new InputArgument($arg, "string");
        }
    }

    /**
     * analyze single simple quoted text argument
     *
     * @param int $index index to start analysis string
     *
     * @return void
     */
    protected function parseSimpleQuote(&$index)
    {
        $arg = '';

        $c = $this->inputString[$index];
        if ($c != "'") {
            $this->setError($this->setError(\ErrorCode::getError('ATTR1205', strlen($this->functionName) + 1 + $index, $this->funcCall)));
        }

        for ($i = $index + 1; $i < strlen($this->inputString); $i++) {
            $cp = $c;
            $c = $this->inputString[$i];

            if ($c == "'") {
                if ($cp == '\\') {
                    $arg = substr($arg, 0, -1);
                    $arg .= $c;
                } else {
                    $arg .= $c;
                }
            } elseif ($c == ',') {
                break;
            } else {
                $arg .= $c;
            }
        }
        $r = strlen($arg) - 1;
        $c = ($r >= 0) ? $arg[$r] : '';
        while ($c == ' ' && $r > 0) {
            $r--;
            $c = $arg[$r];
        }
        if ($c == "'") {
            $arg = substr($arg, 0, $r);
        }
        $index = $i;
        $key = null;
        if (preg_match('/^{([a-z_][a-z0-9_]+)}(.*)$/i', $arg, $reg)) {
            $key = $reg[1];
            $arg = $reg[2];
        }
        if ($key === null) {
            $this->inputs[] = new InputArgument($arg, "string");
        } else {
            $this->inputs[$key] = new InputArgument($arg, "string");
        }
    }
}
