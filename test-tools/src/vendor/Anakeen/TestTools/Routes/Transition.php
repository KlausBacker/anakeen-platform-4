<?php

namespace Anakeen\TestTools\Routes;

use Anakeen\SmartElementManager;
use Anakeen\Wdoc;
use Anakeen\Router\Exception;
use Anakeen\Router\ApiV2Response;

class Transition
{
    /**
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\Response
     */
    public function __invoke(
        \Slim\Http\request $request,
        \Slim\Http\response $response,
        $args
    ) {
        if (!empty($args['transition'])) {
            $transition = Wdoc::getTransition($args['transition']);
        }
    }
}
