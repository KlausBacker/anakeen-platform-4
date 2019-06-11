<?php

namespace Anakeen\Core\Internal\Format;

class DoubleAttributeValue extends FormatAttributeValue
{
    public function __construct(\Anakeen\Core\SmartStructure\NormalAttribute $oa, $v, $decimalSeparator = ',')
    {
        parent::__construct($oa, $v);
        if ($this->value !== null) {
            $lang = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "CORE_LANG");

            if (is_array($this->value)) {
                foreach ($this->value as $k => $v2) {
                    $this->value[$k] = ($v2 !== "") ? doubleval($v2) : null;
                }
                if ($lang == "fr_FR") {
                    foreach ($this->displayValue as $k => $v2) {
                        $this->displayValue[$k] = str_replace('.', $decimalSeparator, $v2);
                    }
                }
            } else {
                $this->value = doubleval($this->value);
                if ($lang == "fr_FR") {
                    $this->displayValue = str_replace('.', $decimalSeparator, $this->displayValue);
                }
            }
        }
    }
}
