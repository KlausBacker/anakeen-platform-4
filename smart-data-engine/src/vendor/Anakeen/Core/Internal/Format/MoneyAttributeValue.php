<?php

namespace Anakeen\Core\Internal\Format;

use Anakeen\Core\ContextManager;

class MoneyAttributeValue extends FormatAttributeValue
{
    public function __construct(\Anakeen\Core\SmartStructure\NormalAttribute $oa, $v)
    {
        parent::__construct($oa, $v);

        if ($this->value !== null) {
            if (is_array($this->displayValue)) {
                foreach ($this->displayValue as $k => $dv) {
                    $this->displayValue[$k] = self::formatMoney('%!.2n', doubleval($dv));
                    if ($oa->format) {
                        $this->displayValue[$k] = sprintf($oa->format, $this->displayValue[$k]);
                    }
                }
            } else {
                $this->displayValue = self::formatMoney(doubleval($v));
                if ($oa->format) {
                    $this->displayValue = sprintf($oa->format, $this->displayValue);
                }
            }

            if (is_array($this->value)) {
                foreach ($this->value as $k => $v) {
                    $this->value[$k] = ($v !== "") ? doubleval($v) : null;
                }
            } else {
                $this->value = doubleval($this->value);
            }
        }
    }

    public static function formatMoney(float $number)
    {
        static $fmts = [];

        $locale = ContextManager::getLanguage();
        if ($locale === "") {
            return (string)$number;
        }
        if (!isset($fmts[$locale])) {
            $fmts[$locale] = $fmt = new \NumberFormatter(ContextManager::getLanguage(), \NumberFormatter::DECIMAL);
            $fmts[$locale]->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 2);
            $fmts[$locale]->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 2);
        }

        return $fmts[$locale]->format($number)?: (string)$number;
    }
}
