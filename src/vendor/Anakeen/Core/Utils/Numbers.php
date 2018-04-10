<?php

namespace Anakeen\Core\Utils;

class Numbers
{
    /**
     * return true it is a number
     * use for constraint
     * @param float $x the number to test
     * @param float $min the minimum of the number (null to indicate no limit)
     * @param float $max the maximum of the number (null to indicate no limit)
     * @return string err if cannot match range
     */
    public static function isFloat($x, $min = null, $max = null)
    {
        $err = "";
        if ($x === "" || $x == '-') {
            return "";
        }
        if (!is_numeric($x)) {
            $err = sprintf(_("[%s] must be a number"), $x);
        }
        if (($min !== null) && ($x < $min)) {
            $err = sprintf(_("[%s] must be greater than %s"), $x, $min);
        }
        if (($max !== null) && ($x > $max)) {
            $err = sprintf(_("[%s] must be lower than %s"), $x, $max);
        }
        return $err;
    }
    /**
     * return true it is a integer
     * use for constraint
     * @param float $x the number to test
     * @param float $min the minimum of the number (null to indicate no limit)
     * @param float $max the maximum of the number (null to indicate no limit)
     * @return string err if cannot match range
     */
    public static function isInteger($x, $min = null, $max = null)
    {
        if ($x === "") {
            return "";
        }
        if (($err = self::isFloat($x, $min, $max)) != "") {
            return $err;
        }
        if (floatval($x) < - floatval(pow(2, 31)) || floatval($x) > floatval(pow(2, 31) - 1)) {
            // signed int32 overflow
            return sprintf(_("[%s] must be between %s and %s"), $x, -floatval(pow(2, 31)), floatval(pow(2, 31) - 1));
        }
        if (intval($x) != floatval($x)) {
            return sprintf(_("[%s] must be a integer"), $x);
        }

        return '';
    }
}
