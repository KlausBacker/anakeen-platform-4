<?php

namespace Anakeen\Core\Utils;

class Date
{
    public static function getNow(bool $useMicrosecond = false): string
    {
        $isoDate = date("d-m-Y H:i:s");
        if ($useMicrosecond === true) {
            $isoDate .= substr(microtime(), 1, 8);
        }
        return $isoDate;
    }


    /**
     * Convert Database date to iso
     * 2018-08-03 11:27:00.396713 => 2018-08-03T11:27:00.
     * @param string $bdData
     * @return string
     */
    public static function rawToIsoDate(string $bdData): string
    {
        return substr($bdData, 0, 10) . 'T' . substr($bdData, 11, 8);
    }
}
