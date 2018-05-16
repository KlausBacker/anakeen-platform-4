<?php

namespace Anakeen\Core\Internal\Format;

use \Anakeen\Core\SEManager;

class MoneyAttributeValue extends FormatAttributeValue
{
    public function __construct(\Anakeen\Core\SmartStructure\NormalAttribute $oa, $v)
    {
        parent::__construct($oa, $v);

        $lang = \Anakeen\Core\ContextManager::getApplicationParam("CORE_LANG");
        if ($lang == "fr_FR") {
        }
        if (is_array($this->displayValue)) {
            foreach ($this->displayValue as $k => $dv) {
                $this->displayValue[$k] = money_format('%!.2n', doubleval($dv));
                if ($oa->format) {
                    $this->displayValue[$k] = sprintf($oa->format, $this->displayValue[$k]);
                }
            }
        } else {
            $this->displayValue = money_format('%!.2n', doubleval($v));
            if ($oa->format) {
                $this->displayValue = sprintf($oa->format, $this->displayValue);
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
