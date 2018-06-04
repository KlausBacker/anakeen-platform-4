<?php

namespace Anakeen\Routes\Middleware;

use \Anakeen\Core\ContextManager;
use Anakeen\LogManager;
use \Dcp\Core\Exception;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;

class Log
{
    /**
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $next
     * @param array               $args
     *
     * @return mixed
     */
    public static function Write($request, $response, $next, $args = [])
    {
        // error_log("Before Middle" . __METHOD__);
        $mb = microtime(true);
        $response = $next($request, $response);
        $duration = intval((microtime(true) - $mb) * 1000);
        /**
         * @var \Slim\Http\response $response
         */
        if (preg_match("/json/", $response->getHeaderLine("content-type"))) {
            $json = json_decode((string)$response->getBody(), true);
            if ($json && !is_array($json)) {
                $json["duration(ms)"] = $duration;
                $response = $response->withJson($json);
            }

        }

        LogManager::warning("Hello", ["duration" => $duration . "ms"]);
        LogManager::error("Houlà", ["duration" => $duration . "ms"]);
        // error_log("After Middel" . __METHOD__);
        return $response;
    }

    public static function Write1($request, $response, $next, $args = [])
    {
        // error_log("Before Middle" . __METHOD__);
        $response = $next($request, $response);
        // error_log("After Middel" . __METHOD__);
        return $response;
    }

    public static function Write2($request, $response, $next, $args = [])
    {
        // error_log("Before Middle" . __METHOD__);
        $response = $next($request, $response);
        // error_log("After Middel" . __METHOD__);
        return $response;
    }

    public static function Write3($request, $response, $next, $args = [])
    {
        // error_log("Before Middle" . __METHOD__);
        $response = $next($request, $response);
        // error_log("After Middel" . __METHOD__);
        return $response;
    }
}