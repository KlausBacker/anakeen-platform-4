<?php

namespace Dcp\Router;

class ApiV2Response
{
    /**
     * Return normalize output for http api
     *
     * @param \Slim\Http\response $response
     * @param mixed               $data
     * @param array               $messages
     *
     * @return \Slim\Http\response
     */
    public static function withData(\Slim\Http\response $response, $data, $messages = [])
    {
        $return = ["success" => true, "data" => $data, "messages" => $messages];

        return $response->withJson($return);
    }
}