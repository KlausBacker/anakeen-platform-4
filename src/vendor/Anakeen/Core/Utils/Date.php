<?php

namespace Anakeen\Core\Utils;

class Date
{
    public static function getNow(bool $useMicrosecond = false): string
    {
        $isoDate = date("Y-m-d H:i:s");
        if ($useMicrosecond === true) {
            $isoDate .= substr(microtime(), 1, 8);
        }
        return $isoDate;
    }

    public static function getDate(): string
    {
        $isoDate = date("Y-m-d");
        return $isoDate;
    }


    /**
     * Convert Database date to iso
     * 2018-08-03 11:27:00.396713 => 2018-08-03T11:27:00.
     *
     * @param string $bdData
     *
     * @return string
     */
    public static function rawToIsoDate(string $bdData): string
    {
        return substr($bdData, 0, 10) . 'T' . substr($bdData, 11, 8);
    }


    /**
     * convert French date to unix timestamp
     * date must be > 01/01/1970 and < 2038
     *
     * @param string $fdate DD/MM/YYYY HH:MM
     *
     * @param bool   $utc use UTC timezone
     *
     * @return float number of second since epoch (return -1 if incorrect date)
     */
    public static function frenchDateToUnixTs($fdate, $utc = false)
    {
        if (preg_match("/^(\d\d)\/(\d\d)\/(\d\d\d\d)\s?(\d\d)?:?(\d\d)?:?(\d\d)?/", $fdate, $r)) {
            $r[4] = isset($r[4]) ? $r[4] : 0;
            $r[5] = isset($r[5]) ? $r[5] : 0;
            $r[6] = isset($r[6]) ? $r[6] : 0;
            if ($utc) {
                $dt = gmmktime($r[4], $r[5], $r[6], $r[2], $r[1], $r[3]);
            } else {
                $dt = mktime($r[4], $r[5], $r[6], $r[2], $r[1], $r[3]);
            }
        } else {
            $dt = -1;
        }
        return $dt;
    }

    public static function stringDateToLocaleDate($fdate, $format = '')
    {
        if (preg_match('/^(\d\d\d\d)-(\d\d)-(\d\d)[\s|T]?(\d\d)?:?(\d\d)?:?(\d\d)?/', $fdate, $r)) {
            // convert to french
            $yy = $r[1];
            $mo = $r[2];
            $dd = $r[3];
            $hh = isset($r[4]) ? $r[4] : null;
            $mm = isset($r[5]) ? $r[5] : null;
            $ss = isset($r[6]) ? $r[6] : null;
            if (!$hh && !$mm && !$ss) {
                $fdate = sprintf("%s/%s/%s", $dd, $mo, $yy);
            } elseif (!$ss) {
                $fdate = sprintf("%s/%s/%s %s:%s", $dd, $mo, $yy, $hh, $mm);
            } else {
                $fdate = sprintf("%s/%s/%s %s:%s:%s", $dd, $mo, $yy, $hh, $mm, $ss);
            }
        }
        return self::dmyDateToLocaleDate($fdate, $format);
    }

    /**
     *
     * @param string $fdate
     *
     * @param string $format strftime format
     *
     * @return string
     */
    public static function dmyDateToLocaleDate($fdate, $format = '')
    {
        if (empty($fdate)) {
            return "";
        }
        if (empty($format)) {
            $localeconfig = \Anakeen\Core\ContextManager::getLocaleConfig();
            if ($localeconfig !== false) {
                if (strlen($fdate) >= 16) {
                    $format = $localeconfig['dateTimeFormat'];
                } else {
                    $format = $localeconfig['dateFormat'];
                }
            } else {
                return $fdate;
            }
        }
        $ldate = $format;
        $d = substr($fdate, 0, 2);
        $m = substr($fdate, 3, 2);
        $y = substr($fdate, 6, 4);
        if (!ctype_digit($d)) {
            return $fdate;
        }
        if (!ctype_digit($m)) {
            return $fdate;
        }
        if (!ctype_digit($y)) {
            return $fdate;
        }
        if (strlen($fdate) >= 16) {
            $h = substr($fdate, 11, 2);
            $i = substr($fdate, 14, 2);
            if (!ctype_digit($h)) {
                return $fdate;
            }
            if (!ctype_digit($i)) {
                return $fdate;
            }
            if (strlen($fdate) == 19) {
                $s = substr($fdate, 17, 2);
                if (!ctype_digit($s)) {
                    return $fdate;
                }
            }
        }
        $ldate = str_replace('%d', $d, $ldate);
        $ldate = str_replace('%m', $m, $ldate);
        $ldate = str_replace('%Y', $y, $ldate);
        if (isset($h)) {
            $ldate = str_replace('%H', $h, $ldate);
        }
        if (isset($i)) {
            $ldate = str_replace('%M', $i, $ldate);
        }
        if (isset($s)) {
            $ldate = str_replace('%S', $s, $ldate);
        }
        return $ldate;
    }

    /**
     * convert French date DD/MM/YYYY to iso
     * date must be > 01/01/1970 and < 2038
     *
     * @param string  $fdate DD/MM/YYYY HH:MM
     * @param boolean $withT return YYYY-MM-DDTHH:MM:SS else YYYY-MM-DD HH:MM:SS
     *
     * @return string  YYYY-MM-DD HH:MM:SS
     */
    public static function dmyDateToIso($fdate, $withT = true)
    {
        if (!$fdate) {
            return '';
        }
        if (preg_match('/^(\d\d)\/(\d\d)\/(\d\d\d\d)\s?(\d\d)?:?(\d\d)?:?(\d\d)?/', $fdate, $r)) {
            if (empty($r[4])) {
                $dt = sprintf("%04d-%02d-%02d", $r[3], $r[2], $r[1]);
            } else {
                $dt = sprintf("%04d-%02d-%02d%s%02d:%02d:%02d", $r[3], $r[2], $r[1], ($withT) ? 'T' : ' ', $r[4], $r[5], $r[6]);
            }
        } else {
            $dt = "";
        }
        return $dt;
    }

    /**
     * convert iso date to unix timestamp
     * date must be > 1970-01-01 and < 2038
     *
     * @param string $isodate YYYY-MM-DD HH:MM
     *
     * @param bool   $utc use UTC timezone
     *
     * @return float number of second since epoch (return -1 if incorrect date)
     */
    public static function iso8601DateToUnixTs($isodate, $utc = false)
    {
        if (preg_match("/^(\d\d\d\d)-(\d\d)-(\d\d)[\s|T]?(\d\d)?:?(\d\d)?:?(\d\d)?/", $isodate, $r)) {
            if (empty($r[4])) {
                $r[4] = 0;
            }
            if (empty($r[5])) {
                $r[5] = 0;
            }
            if (empty($r[6])) {
                $r[6] = 0;
            }
            if ($utc) {
                $dt = gmmktime($r[4], $r[5], $r[6], $r[2], $r[3], $r[1]);
            } else {
                $dt = mktime($r[4], $r[5], $r[6], $r[2], $r[3], $r[1]);
            }
        } else {
            $dt = -1;
        }
        return $dt;
    }

    /**
     * convert date to unix timestamp
     * date must be > 1970-01-01 and < 2038
     *
     * @param string $isodate YYYY-MM-DD HH:MM
     *
     * @param bool   $utc
     *
     * @return float number of second since epoch (return -1 if incorrect date)
     */
    public static function stringDateToUnixTs($isodate, $utc = false)
    {
        $dt = self::FrenchDateToUnixTs($isodate, $utc);
        if ($dt < 0) {
            $dt = self::iso8601DateToUnixTs($isodate, $utc);
        }
        return $dt;
    }

    /**
     * verify if a date seems valid
     *
     * @param string $date iso, french english date
     *
     * @return bool true if it ia a valid date
     */
    public static function isValidDate($date)
    {
        if ((strlen($date) > 0) && (strlen($date) < 3)) {
            return false;
        }
        $date = self::stringDateToIso($date, "");
        if (preg_match('/^(\d\d\d\d)-(\d\d)-(\d\d)/', $date, $reg)) {
            return checkdate($reg[2], $reg[3], $reg[1]);
        }
        return false;
    }

    /**
     * convert string date to iso
     *
     * @note if the $date input is not recognised like a date the function return the original $date argument
     *
     * @param string $date  DD/MM/YYYY HH:MM or YYYY-MM-DD HH:MM or YYYY-MM-DDTHH:MM
     * @param bool|string optionnal input $format to indicate locale : french default is "%d/%m/%Y %H:%M". If not set use locale configuration of server
     * @param bool   $withT to add a T between day and hour: YYYY-MM-DDTHH:MM
     *
     * @return string YYYY-MM-DD HH:MM
     */
    public static function stringDateToIso($date, $format = false, $withT = false)
    {
        if ($format === false) {
            if (preg_match('/^(\d\d\d\d)-(\d\d)-(\d\d)[\s|T]?(\d\d)?:?(\d\d)?:?(\d\d)?/', $date, $r)) {
                if ($withT) {
                    if (strlen($date) > 11) {
                        $date[10] = 'T';
                    }
                }
                return $date;
            } else {
                $dt = self::dmyDateToIso($date, $withT);
                if (!$dt) {
                    return $date;
                }
                return $dt;
            }
        }
        if (empty($format)) {
            $localeconfig = \Anakeen\Core\ContextManager::getLocaleConfig();
            if ($localeconfig !== false) {
                $format = $localeconfig['dateTimeFormat'];
                if (strlen($date) < strlen($format)) {
                    $format = $localeconfig['dateFormat'];
                }
            }
        }
        if (!empty($format)) {
            $format = str_replace('%Y', '%YYY', $format);
            if (strlen($date) < strlen($format)) {
                return $date;
            }
            // date
            $d = strpos($format, '%d');
            $m = strpos($format, '%m');
            $y = strpos($format, '%YYY');
            if ($d !== false && $m !== false && $y !== false) {
                $tmp = substr($date, $y, 4);
                if (!ctype_digit($tmp)) {
                    return $date;
                }
                $dt = $tmp . '-';
                $tmp = substr($date, $m, 2);
                if (!ctype_digit($tmp)) {
                    return $date;
                }
                $dt .= $tmp . '-';
                $tmp = substr($date, $d, 2);
                if (!ctype_digit($tmp)) {
                    return $date;
                }
                $dt .= $tmp;
            } else {
                return $date;
            }
            // time
            $h = strpos($format, '%H');
            $m = strpos($format, '%M');
            $s = strpos($format, '%S');
            if ($h !== false && $m !== false) {
                $dt .= ($withT ? 'T' : ' ') . substr($date, $h, 2) . ':' . substr($date, $m, 2);
                if ($s !== false) {
                    $dt .= ':' . substr($date, $s, 2);
                }
            }
            return $dt;
        } else {
            $dt = self::dmyDateToIso($date, $withT);
            if (!$dt) {
                return $date;
            }
            return $dt;
        }
    }
}
