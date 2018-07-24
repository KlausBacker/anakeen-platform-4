<?php

namespace Anakeen\Core\Internal;

class Debug
{

    /**
     * return call stack
     *
     * @param int $slice last call to not return
     * @param int $max   max call stack
     * @return array
     */
    public static function getDebugStack($slice = 1, $max = -1)
    {
        $td = @debug_backtrace(false);
        if (!is_array($td)) {
            return array();
        }
        $t = array_slice($td, $slice);
        foreach ($t as $k => $s) {
            unset($t[$k]["args"]); // no set arg
        }
        if ($max > 0) {
            $t = array_slice($t, 0, $max);
        }
        return $t;
    }

    public static function getDebugStackString($slice = 1, $max = -1)
    {
        $t = self::getDebugStack($slice, $max);
        $s = [];
        foreach ($t as $info) {
            if (!isset($info["line"])) {
                $s[] = sprintf("%s%s%s", $info["class"], $info["type"], $info["function"]);
            } else {
                if (isset($info["type"])) {
                    $s[] = sprintf("%s:%s %s%s%s", $info["file"], $info["line"], $info["class"], $info["type"], $info["function"]);
                } else {
                    $s[] = sprintf("%s:%s %s", $info["file"], $info["line"], $info["function"]);
                }
            }
        }
        return implode("\n", $s);
    }
}
