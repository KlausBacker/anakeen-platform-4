<?php

namespace Anakeen\Routes\Middleware;

class Fake
{
    public function __invoke($request, $response, $next, $args = [])
    {
        error_log("Before Middle" . __METHOD__. implode(", ", $args));
        $response = $next($request, $response);
        error_log("After Middle" . __METHOD__);
        return $response;
    }
}
