<?php

namespace Anakeen\Core\Utils;

class Strings
{
    /**
     * like ucfirst for utf-8
     * @param $s
     *
     * @return string
     */
    public static function mb_ucfirst($s)
    {
        if ($s) {
            $s = mb_strtoupper(mb_substr($s, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($s, 1, mb_strlen($s), 'UTF-8');
        }
        return $s;
    }

    /**
     * muti-byte trim
     * @param $string
     *
     * @return null|string|string[]
     */
    public static function mb_trim($string)
    {
        return preg_replace("/(^\s+)|(\s+$)/us", "", $string);
    }

    /**
     * Returns <kbd>true</kbd> if the string or array of string is encoded in UTF8.
     *
     * Example of use. If you want to know if a file is saved in UTF8 format :
     * <code> $array = file('one file.txt');
     * $isUTF8 = isUTF8($array);
     * if (!$isUTF8) --> we need to apply utf8_encode() to be in UTF8
     * else --> we are in UTF8 :)
     * </code>
     *
     * @param mixed $string , or an array from a file() function.
     *
     * @return boolean
     */
    public static function isUTF8($string)
    {
        if (is_array($string)) {
            return self::seemsUTF8(implode('', $string));
        } else {
            return self::seemsUTF8($string);
        }
    }

    /**
     * Returns <kbd>true</kbd> if the string  is encoded in UTF8.
     *
     * @param mixed $Str string
     *
     * @return boolean
     */
    public static function seemsUTF8($Str)
    {
        return preg_match('!!u', $Str);
    }
}
