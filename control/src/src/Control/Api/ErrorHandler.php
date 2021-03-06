<?php

namespace Control\Api;

class ErrorHandler
{
    const status=400;
    const message="Anakeen Control Runtime";
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $exception)
    {
        if (is_a($exception, \Control\Exception\RuntimeException::class)) {
            /**
             * @var \Control\Exception\RuntimeException $exception
             */
            $response = $response->withStatus(self::status, $exception->getMessage());
            return $response->withJson($exception);
        }
        /**
         * @var \Control\Exception\RuntimeException $exception
         */
        $response = $response->withStatus(self::status, $exception->getMessage());
        return $response->write(print_r($exception, true));
    }
}
