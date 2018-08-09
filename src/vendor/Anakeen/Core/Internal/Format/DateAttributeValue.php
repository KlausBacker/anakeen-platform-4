<?php

namespace Anakeen\Core\Internal\Format;

class DateAttributeValue extends StandardAttributeValue
{
    const defaultStyle = 'D';
    /**
     * ISO with T : YYYY-MM-DDTHH:MM:SS
     */
    const isoStyle = 'I';
    /**
     * ISO without T : YYYY-MM-DD HH:MM:SS
     */
    const isoWTStyle = 'U';
    const frenchStyle = 'F';

    public function __construct(\Anakeen\Core\SmartStructure\NormalAttribute $oa, $v, $dateStyle = self::defaultStyle)
    {
        parent::__construct($oa, $v);
        if ($oa->format != "") {
            $this->displayValue = strftime($oa->format, stringDateToUnixTs($v));
        } else {
            if ($dateStyle === self::defaultStyle) {
                $this->displayValue = stringDateToLocaleDate($v);
            } elseif ($dateStyle === self::isoStyle) {
                $this->displayValue = stringDateToIso($v, false, true);
            } elseif ($dateStyle === self::isoWTStyle) {
                $this->displayValue = stringDateToIso($v, false, false);
            } elseif ($dateStyle === self::frenchStyle) {
                $ldate = stringDateToLocaleDate($v, '%d/%m/%Y %H:%M');
                if (strlen($v) < 11) {
                    $this->displayValue = substr($ldate, 0, strlen($v));
                } else {
                    $this->displayValue = $ldate;
                }
            } else {
                $this->displayValue = stringDateToLocaleDate($v);
            }
        }
        if ($oa->type === "timestamp") {
            $this->value[10]='T';
        }
    }
}
