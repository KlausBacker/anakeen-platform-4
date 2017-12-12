<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\HttpApi\V1;

class DebugTrace
{
    private static $debug = array();
    
    public static function addTrace($message)
    {
        static $previous;
        static $start;
        
        $mb = microtime(true);
        if ($previous) {
            $delay = $mb - $previous;
        } else {
            $delay = 0;
            $start = $mb;
        }
        $previous = $mb;
        self::$debug[] = sprintf("%.03fms %.03fms: %s;", $delay, ($mb - $start), $message);
    }
    
    public static function getTraces()
    {
        return self::$debug;
    }
}
