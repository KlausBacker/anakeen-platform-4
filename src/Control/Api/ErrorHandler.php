<?php


namespace Control\Api;


class ErrorHandler
{
    const status=400;
    const message="Anakeen Control Runtime";
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $exception)
    {
        if (is_a($exception, \Control\Api\ApiRuntimeException::class)) {
            /**
             * @var \Control\Api\ApiRuntimeException $exception
             */
            $response = $response->withStatus(self::status, self::message);
            return $response->withJson($exception);
        }
        throw $exception;
    }
}