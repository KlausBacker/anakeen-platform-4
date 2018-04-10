<?php

namespace Anakeen\Core\Internal;


class Debug
{

    /**
     * return call stack
     *
     * @param int $slice last call to not return
     *
     * @return array
     */
    public static function getDebugStack($slice = 1)
    {
        $td = @debug_backtrace(false);
        if (!is_array($td)) {
            return array();
        }
        $t = array_slice($td, $slice);
        foreach ($t as $k => $s) {
            unset($t[$k]["args"]); // no set arg
        }
        return $t;
    }
}