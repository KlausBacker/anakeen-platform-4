<?php

namespace Anakeen\Core\Internal\Format;

use \Anakeen\Core\DocManager;

class DoubleAttributeValue extends FormatAttributeValue
{
    public function __construct(\Anakeen\Core\SmartStructure\NormalAttribute $oa, $v, $decimalSeparator = ',')
    {
        parent::__construct($oa, $v);
        $lang = \Anakeen\Core\ContextManager::getApplicationParam("CORE_LANG");
        if ($lang == "fr_FR") {
            if (is_array($this->displayValue)) {
                foreach ($this->displayValue as $k => $v) {
                    $this->displayValue[$k] = str_replace('.', $decimalSeparator, $v);
                }
            } else {
                $this->displayValue = str_replace('.', $decimalSeparator, $this->displayValue);
            }
        }
        if (is_array($this->value)) {
            /** @noinspection PhpWrongForeachArgumentTypeInspection */
            foreach ($this->value as $k => $v) {
                $this->value[$k] = doubleval($v);
            }
        } else {
            $this->value = doubleval($this->value);
        }
    }
}
