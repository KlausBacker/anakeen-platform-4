<?php

namespace Anakeen\Core\SmartStructure\Callables;

class ParseFamilyMethod extends ParseFamilyFunction
{
    public $methodName = '';
    public $className = '';

    /**
     * @static
     * @param      $methCall
     * @param bool $noOut
     * @return \Anakeen\Core\SmartStructure\Callables\ParseFamilyMethod
     */
    public function parse($methCall, $noOut = false)
    {
        $this->initParse($methCall);

        $methodName = trim(substr($methCall, 0, $this->firstParenthesis));
        if ($this->checkParenthesis()) {
            if (strpos($methodName, '::') === false) {
                if (class_exists($methodName)) {
                    if (method_exists(new $methodName(), "__invoke")) {
                        $this->className = $methodName;
                        $this->methodName = "__invoke";
                    } else {
                        $this->setError(\ErrorCode::getError('ATTR1256', $methCall));
                        return $this;
                    }
                } else {
                    $this->setError(\ErrorCode::getError('ATTR1251', $methCall));
                    return $this;
                }
            } else {
                list($this->className, $this->methodName) = explode('::', $methodName, 2);
            }
            if (!$this->isPHPName($this->methodName)) {
                $this->setError(\ErrorCode::getError('ATTR1252', $this->methodName));
            } elseif ($this->className && (!$this->isPHPClassName($this->className))) {
                $this->setError(\ErrorCode::getError('ATTR1253', $this->className));
            } else {
                $inputString = substr($methCall, $this->firstParenthesis + 1, ($this->lastParenthesis - $this->firstParenthesis - 1));
                $this->inputString = $inputString;

                $this->parseArguments();
                $this->parseOutput();
                if ($noOut) {
                    $this->limitOutputToZero();
                } else {
                    $this->limitOutputToOne();
                }
            }
        }

        return $this;
    }

    protected function limitOutputToOne()
    {
        if (count($this->outputs) > 1) {
            $this->setError(\ErrorCode::getError('ATTR1254', $this->funcCall));
        }
    }

    protected function limitOutputToZero()
    {
        if (count($this->outputs) > 0) {
            $this->setError(\ErrorCode::getError('ATTR1255', $this->funcCall));
        } elseif ($this->lastSemiColumn > $this->lastParenthesis) {
            $this->setError(\ErrorCode::getError('ATTR1255', $this->funcCall));
        }
    }
}
