<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp;

if (!defined("ALTSEPCHAR")) {
    define("ALTSEPCHAR", ' --- ');
}

class WriteCsv
{
    public static $enclosure = '';
    public static $separator = ';';
    public static $encoding = "utf-8";

    /**
     * @param resource $handler
     * @param array    $data
     */
    public static function fput($handler, array $data)
    {
        foreach ($data as $k => $datum) {
            $data[$k] = self::flatValue($datum);
        }
        if (empty(self::$enclosure)) {
            //str_replace(SEPCHAR, ALTSEPCHAR
            $cleanData = array_map(function ($item) {
                return str_replace(array(
                    "\n",
                    self::$separator,
                    "\r"
                ), array(
                    "\\n",
                    ALTSEPCHAR,
                    ""
                ), $item);
            }, $data);
            $s = implode(self::$separator, $cleanData);
            if (self::$encoding === "iso8859-15") {
                $s = utf8_decode($s);
            }
            fputs($handler, $s . "\n");
        } else {
            if (self::$encoding === "iso8859-15") {
                $data = array_map(function ($cell) {
                    return utf8_decode($cell);
                }, $data);
            }
            fputcsv($handler, $data, self::$separator, self::$enclosure);
        }
    }

    public static function flatValue($v)
    {
        if (is_array($v)) {
            $v = implode("\n", $v);
        }
        return $v;
    }
}
