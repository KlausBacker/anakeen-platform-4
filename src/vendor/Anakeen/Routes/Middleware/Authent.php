<?php

namespace Dcp\Routes\Middleware;

use \Dcp\Core\ContextManager;
use \Dcp\Core\Exception;

class Authent
{
    public static function Authenticate($request, $response, $next, $args = [])
    {
        error_log("Before inside" . __METHOD__);

        error_log("Exec Middle" . __METHOD__. " : ". print_r($args, true));
        $response = $next($request, $response);
        error_log("After inside" . __METHOD__);
        return $response;
    }
}