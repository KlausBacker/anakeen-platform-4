<?php

namespace Anakeen\Routes;

/**
 * Fake
 */
class Fake
{
    /**
     *
     *
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $args
     *
     * @return \Slim\Http\response $response
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        return $response->withJson($args);
    }
}
